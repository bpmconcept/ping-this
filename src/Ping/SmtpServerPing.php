<?php

namespace PingThis\Ping;

class SmtpServerPing extends AbstractPing
{
    protected $timeout;
    protected $host;
    protected $port;
    protected $error;

    /**
     * @param $frequency
     * @param $host           SMTP server hostname or IP
     * @param $port           SMTP server port
     * @param $timeout        Connection timeout
     */
    public function __construct($frequency, $host, $port = 25, $timeout = 3)
    {
        parent::__construct($frequency);

        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
    }

    public function getName()
    {
        return sprintf('Check SMTP server at %s:%d', $this->host, $this->port);
    }

    public function getLastError()
    {
        if (null !== $this->error) {
            return $this->error;
        } else {
            return 'Command failed';
        }
    }

    public function ping()
    {
        if (!$stream = @stream_socket_client(sprintf('tcp://%s:%d', $this->host, $this->port), $errno, $errstr, $this->timeout)) {
            $this->error = sprintf('Stream socket connection failed: "%s"', $errstr);
            return false;
        }

        stream_set_timeout($stream, $this->timeout);
        $greeting = fgets($stream);
        fclose($stream);

        if (!preg_match('/^220 /', $greeting)) {
            $this->error = 'Unvalid SMTP response: ' . $greeting;
            return false;
        }

        return true;
    }
}
