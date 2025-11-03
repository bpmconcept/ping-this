<?php

namespace PingThis\Ping;

/**
 * Check the expiration date of a remote server's certificate. This ping requires
 * the openssl module.
 *
 * @todo Use ExpressionLanguage for date comparison and more
 */
class TlsCertificateExpirationPing extends AbstractPing
{
    const IMPLICIT_TLS = 'tls';
    const STARTTLS_SMTP = 'smtp';
    const STARTTLS_IMAP = 'imap';

    private ?\DateTime $date = null;
    private ?string $error = null;

    public function __construct(
        int $frequency,
        private readonly string $host,
        private readonly int $port,
        private readonly string $protocol,
        private string $threshold
    )
    {
        if (!function_exists('openssl_x509_parse')) {
            trigger_error('TlsCertificateExpirationPing requires the OpenSSL module', E_USER_ERROR);
        }

        parent::__construct($frequency);
    }

    public function setThreshold(string $threshold): void
    {
        $this->threshold = $threshold;
    }

    public function getName(): string
    {
        return sprintf('TLS certificate validity on %s:%d', $this->host, $this->port);
    }

    public function getLastError(): ?string
    {
        return $this->error;
    }

    public function ping(): bool
    {
        try {
            set_error_handler(static function ($severity, $message): void {
                throw new \RuntimeException($message);
            });

            $socket = $this->createSocket();
            $this->initialize($socket);
            $this->startTls($socket);
            $this->date = $this->getCertificateExpirationDate($socket);

            if ($this->date < new \DateTime($this->threshold)) {
                $this->error = sprintf('Certificate expires on %s', $this->date->format('Y-m-d H:i:s'));
                return false;
            }

            return true;
        } catch (\RuntimeException $e) {
            $this->error = $e->getMessage();
            return false;
        } finally {
            restore_error_handler();
        }
    }

    protected function createSocket()
    {
        $timeout = min(10, $this->getPingFrequency());
        $context = stream_context_create(['ssl' => ['capture_peer_cert' => true]]);

        if (false === ($socket = @stream_socket_client(sprintf('tcp://%s:%d', $this->host, $this->port), $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context))) {
            throw new \RuntimeException($errstr);
        }

        return $socket;
    }

    protected function initialize($socket): void
    {
        match ($this->protocol) {
            self::STARTTLS_SMTP => $this->initializeSmtp($socket),
            self::STARTTLS_IMAP => $this->initializeImap($socket),
            default => null,
        };
    }

    protected function startTls($socket): void
    {
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT);
    }

    protected function getCertificateExpirationDate($socket)
    {
        $certificate = stream_context_get_params($socket);
        $infos = openssl_x509_parse($certificate['options']['ssl']['peer_certificate']);

        return new \DateTime('@'.$infos['validTo_time_t']);
    }

    private function initializeSmtp($socket): void
    {
        fread($socket, 2048);
        fwrite($socket, "EHLO ping-this\n");
        fread($socket, 2048);
        fwrite($socket, "STARTTLS\n");
        fgets($socket);
    }

    private function initializeImap($socket): void
    {
        fread($socket, 2048);
        fwrite($socket, ". STARTTLS\n");
        fread($socket, 2048);
    }
}
