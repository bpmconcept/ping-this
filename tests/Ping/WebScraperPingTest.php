<?php

use PingThis\Ping\WebScraperPing;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\DomCrawler\Crawler;

class WebScraperPingTest extends \PHPUnit\Framework\TestCase
{
    public function testScraperResponseOnly()
    {
        $ping = $this->getMockBuilder('PingThis\Ping\WebScraperPing')
            ->setConstructorArgs([0, 'GET', 'https://www.google.com', function (Response $response) {
                return $response->getStatusCode() == 200;
            }])
            ->setMethods(['doRequest'])
            ->getMock();

        $ping->expects($this->any())
             ->method('doRequest')
             ->will($this->onConsecutiveCalls(
                [new Crawler(), new Response('content', 200)],
                [new Crawler(), new Response('content', 404)]
             ));

        $this->assertTrue($ping->ping());
        $this->assertFalse($ping->ping());
    }

    public function testScraperResponseAndCrawler()
    {
        $ping = $this->getMockBuilder('PingThis\Ping\WebScraperPing')
            ->setConstructorArgs([0, 'GET', 'https://www.google.com', function (Response $response, Crawler $crawler) {
                return $crawler !== null && $response->getStatusCode() == 200;
            }])
            ->setMethods(['doRequest'])
            ->getMock();

        $ping->expects($this->any())
             ->method('doRequest')
             ->will($this->onConsecutiveCalls(
                [new Crawler(), new Response('content', 200)],
                [new Crawler(), new Response('content', 404)]
             ));

        $this->assertTrue($ping->ping());
        $this->assertFalse($ping->ping());
    }

    public function testScraperExpression()
    {
        $ping = $this->getMockBuilder('PingThis\Ping\WebScraperPing')
            ->setConstructorArgs([0, 'GET', 'https://www.google.com', 'response.getStatusCode() == 200 and crawler != null'])
            ->setMethods(['doRequest'])
            ->getMock();

        $ping->expects($this->any())
             ->method('doRequest')
             ->will($this->onConsecutiveCalls(
                [new Crawler(), new Response('content', 200)],
                [new Crawler(), new Response('content', 404)]
             ));

        $this->assertTrue($ping->ping());
        $this->assertFalse($ping->ping());
    }

    public function testScraperError()
    {
        $ping = $this->getMockBuilder('PingThis\Ping\WebScraperPing')
            ->setConstructorArgs([0, 'GET', 'https://www.google.com', function (Response $response, Crawler $crawler, &$error) {
                $error = $response->getContent();
                return false;
            }])
            ->setMethods(['doRequest'])
            ->getMock();

        $ping->expects($this->any())
             ->method('doRequest')
             ->will($this->onConsecutiveCalls(
                [new Crawler(), new Response('test1', 200)],
                [new Crawler(), new Response('test2', 404)]
             ));

        $this->assertFalse($ping->ping());
        $this->assertEquals('test1', $ping->getLastError());

        $this->assertFalse($ping->ping());
        $this->assertEquals('test2', $ping->getLastError());
    }
}
