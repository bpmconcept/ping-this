<?php

namespace PingThis\Ping;

/**
 * Check if a given web server correctly answers to HTTP request.
 */
class HttpPing extends WebScraperPing
{
	public function __construct($frequency, $method, $uri, $code = 200)
    {
        parent::__construct($frequency, $method, $uri, "response.getStatus() == $code");
    }
}
