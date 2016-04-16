<?php

use PingThis\Ping\HttpHeaderPing;

class HttpHeaderPingTest extends PHPUnit_Framework_TestCase
{
    public function testHeaders()
    {
        $ping = $this->getMockBuilder('PingThis\Ping\HttpHeaderPing')
            ->setConstructorArgs([0, 'http://test', 200])
            ->setMethods(['getHeaders'])
            ->getMock();
        
        $ping->expects($this->any())
             ->method('getHeaders')
             ->will($this->onConsecutiveCalls(
                [
                    0 => 'HTTP/1.1 200 OK',
                    'Connection' => 'close',
                    'Custom-Header' => 'OK 123',
                ],
                [
                    0 => 'HTTP/1.1 200 OK',
                    'Connection' => 'close',
                    'Custom-Header' => 'KO 345',
                ],
                [
                    0 => 'HTTP/1.1 200 OK',
                    'Connection' => 'close',
                    'Custom-Header' => 'OK 345',
                ],
                [
                    0 => 'HTTP/1.1 500 Internal Server Error',
                    'Connection' => 'close',
                ]
             ));
        
        $ping->setExpectedHeader('custom-header', '/^OK \d+$/');
        
        $this->assertTrue($ping->ping());
        $this->assertFalse($ping->ping());
        $this->assertTrue($ping->ping());
        $this->assertFalse($ping->ping());
    }
}
