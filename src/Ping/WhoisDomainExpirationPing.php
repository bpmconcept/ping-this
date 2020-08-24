<?php

namespace PingThis\Ping;

class WhoisDomainExpirationPing extends StreamSocketCommandPing
{
    protected $domain;
    protected $server;
    protected $threshold;

    public function __construct(int $frequency, string $domain, string $server, string $threshold)
    {
        $this->domain = $domain;
        $this->server = $server;
        $this->threshold = $threshold;

        parent::__construct($frequency, "tcp://$server:43", "$domain\r\n", [$this, 'checkDate']);
    }

    public function getName(): string
    {
        return sprintf('Domain expiration date of %s', $this->domain);
    }

    protected function checkDate($response, &$error)
    {
        if (!preg_match('/.*(expiration|expiry) date.*: ([0-9TZ:\.\-]+)/i', $response, $matches)) {
            $error = "Expiration date not found";
            return false;
        }

        if (new \DateTime($matches[2]) < new \DateTime($this->threshold)) {
            $this->error = sprintf('Domain expires on %s', $this->date->format('Y-m-d H:i:s'));
            return false;
        }

        return true;
    }
}
