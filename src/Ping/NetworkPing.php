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
    const METHOD_RAW_SOCKET = 'socket';

    protected int $ttl = 64;
    protected int $timeout = 3;
    protected ?int $port = null;
    protected string $method;
    protected ?float $latency = null;

    public function __construct(int $frequency, private readonly string $host)
    {
        if (!class_exists('JJG\\Ping')) {
            trigger_error('NetworkPing requires "geerlingguy/ping" package installed', E_USER_ERROR);
        }

        parent::__construct($frequency);
        $this->method = self::METHOD_SYSTEM_PING;
    }

    public function setTtl(int $ttl): void
    {
        $this->ttl = $ttl;
    }

    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    public function setMethod(string $method): void
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

        $method = match ($this->method) {
            self::METHOD_SOCKET, self::METHOD_RAW_SOCKET, self::METHOD_SYSTEM_PING => $this->method,
            default => self::METHOD_SYSTEM_PING,
        };

        return false !== $ping->ping($method);
    }
}
