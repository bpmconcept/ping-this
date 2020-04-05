<?php

namespace PingThis\Ping;

use JJG\Ping;

/**
 * Check if a given host responds to a standard network ping. For systems
 * that do not allow calls to system's ping executable, a socket replacement
 * method may be used.
 *
 * @todo Use ExpressionLanguage for access to latency info
 */
class NetworkPing extends AbstractPing
{
    const METHOD_SYSTEM_PING = 'exec';
    const METHOD_SOCKET = 'fsockopen';

    protected $host;
    protected $ttl = 64;
    protected $timeout = 3;
    protected $port;
    protected $method;
    protected $latency;

    public function __construct(int $frequency, string $host)
    {
        if (!class_exists('JJG\\Ping')) {
            trigger_error('NetworkPing requires "geerlingguy/ping" package installed', E_USER_ERROR);
        }

        parent::__construct($frequency);
        $this->host = $host;
        $this->method = self::METHOD_SYSTEM_PING;
    }

    public function setTtl($ttl)
    {
        $this->ttl = $ttl;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    public function setPort($port)
    {
        $this->port = $port;
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }

    public function getName(): string
    {
        return sprintf('Ping request on %s', $this->host);
    }

    public function getLastError(): ?string
    {
        return sprintf('Host %s is unreachable', $this->host);
    }

    public function ping(): bool
    {
        $ping = new Ping($this->host);
        $ping->setTtl($this->ttl);
        $ping->setTimeout($this->timeout);

        if ($this->port !== null) {
            $ping->setPort($this->port);
        }

        return false !== $ping->ping($this->method ?: '');
    }
}
