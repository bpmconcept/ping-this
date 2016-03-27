<?php

namespace MarcBP\PingThis\Ping;

use JJG\Ping;

class NetworkPing extends AbstractPing
{
	const METHOD_SYSTEM_PIN = 'exec';
	const METHOD_SOCKET = 'fsockopen';

	protected $ping;
	protected $method;
	protected $latency;

	public function __construct($name, $frequency, $host)
    {
		if (!class_exists('JJG\\Ping')) {
			trigger_error('NetworkPing requires "geerlingguy/ping" package installed', E_USER_ERROR);
		}

        parent::__construct($name, $frequency);
		$this->ping = new Ping($host);
    }

	public function setTtl($ttl)
	{
		$this->ping->setTtl($ttl);
	}

	public function setPort($port)
	{
		$this->ping->setPort($port);
	}

	public function setMethod($method)
	{
		$this->method = $method;
	}

    public function getLastError()
	{
		return sprintf('Host %s did not respond', $this->ping->getHost());
	}

    public function ping()
	{
		return false !== $this->ping->ping($this->method ?: '');
	}
}
