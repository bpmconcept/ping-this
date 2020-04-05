<?php

namespace PingThis\Ping;

class ImapServerPing extends AbstractPing
{
    protected $timeout;
    protected $host;
    protected $port;
    protected $error;

    /**
     * @param $frequency
     * @param $host           IMAP server hostname or IP
     * @param $port           IMAP server port
     * @param $timeout        Connection timeout
     */
    public function __construct(int $frequency, string $host, int $port = 143, int $timeout = 3)
    {
        parent::__construct($frequency);

        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
    }

    public function getName(): string
    {
        return sprintf('Check IMAP server at %s:%d', $this->host, $this->port);
    }

    public function getLastError(): string
    {
        return $this->error ?: 'Command failed';
    }

    public function ping(): bool
    {
        if (!$stream = @stream_socket_client(sprintf('tcp://%s:%d', $this->host, $this->port), $errno, $errstr, $this->timeout)) {
            $this->error = sprintf('Stream socket connection failed: "%s"', $errstr);
            return false;
        }

        stream_set_timeout($stream, $this->timeout);
        $hello = fgets($stream);
        fclose($stream);

        if (!preg_match('/^\\* OK/', $hello)) {
            $this->error = 'Unvalid IMAP response: ' . $hello;
            return false;
        }

        return true;
    }
}
