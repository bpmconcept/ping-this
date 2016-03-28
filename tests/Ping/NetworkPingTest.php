<?php

use MarcBP\PingThis\Ping\NetworkPing;

class NetworkPingTest extends PHPUnit_Framework_TestCase
{
    public function testSystemPing()
    {
        $ping = new NetworkPing(0, '127.0.0.1');
        $ping->setMethod(NetworkPing::METHOD_SYSTEM_PING);
        $this->assertTrue($ping->ping());
    }
    
    public function testSocketPing()
    {
        $ping = new NetworkPing(0, 'google.com');
        $ping->setMethod(NetworkPing::METHOD_SOCKET);
        $ping->setPort(80);
        $this->assertTrue($ping->ping());
    }
    
    public function testPingUnvalid()
    {
        $ping = new NetworkPing(0, 'does.not.exist');
        $this->assertFalse($ping->ping());
    }
}
