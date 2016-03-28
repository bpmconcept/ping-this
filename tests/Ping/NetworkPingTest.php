<?php

use MarcBP\PingThis\Ping\NetworkPing;

class NetworkPingTest extends PHPUnit_Framework_TestCase
{
    public function testPingOk()
    {
        $ping = new NetworkPing('test', 0, 'google.com');
        $ping->setMethod(NetworkPing::METHOD_SOCKET);
        $ping->setPort(80);
        $this->assertTrue($ping->ping());
    }
    
    public function testPingUnvalid()
    {
        $ping = new NetworkPing('test', 0, 'does.not.exist');
        $ping->setMethod(NetworkPing::METHOD_SOCKET);
        $this->assertFalse($ping->ping());
    }
}
