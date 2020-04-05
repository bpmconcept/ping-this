<?php

namespace PingThis\Ping;

/**
 * Check if a given web server correctly answers to HTTP request.
 */
class HttpPing extends WebScraperPing
{
    public function __construct(int $frequency, string $method, string $uri, int $code = 200)
    {
        parent::__construct($frequency, $method, $uri, "response.getStatusCode() === $code");
    }
}
