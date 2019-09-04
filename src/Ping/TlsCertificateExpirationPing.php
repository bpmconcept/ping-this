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
    const TLS = 'tls';
    const STARTTLS_SMTP = 'smtp';

    protected $host;
    protected $port;
    protected $protocol;
    protected $threshold;
    protected $date;
    protected $error;

    public function __construct($frequency, $host, $port, $protocol, $threshold)
    {
        if (!function_exists('openssl_x509_parse')) {
            trigger_error('TlsCertificateExpirationPing requires the OpenSSL module', E_USER_ERROR);
        }

        parent::__construct($frequency);

        $this->host = $host;
        $this->port = $port;
        $this->protocol = $protocol;
        $this->threshold = $threshold;
    }

    public function setThreshold($threshold)
    {
        $this->threshold = $threshold;
    }

    public function getName()
    {
        return sprintf('TLS certificate validity on %s:%d', $this->host, $this->port);
    }

    public function getLastError()
    {
        return $this->error;
    }

    public function ping()
    {
        try {
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
        }
    }

    protected function createSocket()
    {
        $timeout = min(10, $this->getPingFrequency());
        $context = stream_context_create(['ssl' => ['capture_peer_cert' => TRUE]]);

        if (false === ($socket = @stream_socket_client(sprintf('tcp://%s:%d', $this->host, $this->port), $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context))) {
            throw new \RuntimeException($errstr);
        }

        return $socket;
    }

    protected function initialize($socket)
    {
        if ($this->protocol === self::STARTTLS_SMTP) {
            $welcome = fread($socket, 2048);
            fwrite($socket, "EHLO ping-this\n");
            $helo = fread($socket, 2048);
            fwrite($socket, "STARTTLS\n");
            $starttls = fgets($socket);
        }
    }

    protected function startTls($socket)
    {
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT);
    }

    protected function getCertificateExpirationDate($socket)
    {
        $certificate = stream_context_get_params($socket);
        $infos = openssl_x509_parse($certificate['options']['ssl']['peer_certificate']);

        return new \DateTime('@'.$infos['validTo_time_t']);
    }
}
