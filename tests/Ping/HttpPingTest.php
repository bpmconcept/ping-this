<?php

use PingThis\Ping\HttpPing;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\DomCrawler\Crawler;

class HttpPingTest extends \PHPUnit\Framework\TestCase
{
    public function testPingSucceedsWhenStatusCodeMatches(): void
    {
        $ping = $this->getMockBuilder(HttpPing::class)
            ->setConstructorArgs([1, 'GET', 'https://example.com', 200])
            ->onlyMethods(['doRequest'])
            ->getMock();

        $ping->method('doRequest')->willReturn([new Crawler(), new Response('ok', 200)]);

        $this->assertTrue($ping->ping());
        $this->assertNull($ping->getLastError());
    }

    public function testPingFailsAndSetsErrorWhenStatusCodeDiffers(): void
    {
        $ping = $this->getMockBuilder(HttpPing::class)
            ->setConstructorArgs([1, 'GET', 'https://example.com', 200])
            ->onlyMethods(['doRequest'])
            ->getMock();

        $ping->method('doRequest')->willReturn([new Crawler(), new Response('fail', 500)]);

        $this->assertFalse($ping->ping());
        $this->assertSame('Unvalid response code 500', $ping->getLastError());
    }
}
