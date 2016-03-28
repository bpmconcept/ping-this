<?php

use MarcBP\PingThis\Ping\NetworkPing;

class NetworkPingTest extends PHPUnit_Framework_TestCase
{
    public function testPingOk()
    {
        $ping = new NetworkPing('test', 0, '127.0.0.1');
        $ping->setMethod(NetworkPing::METHOD_SOCKET);
        $this->assertTrue($ping->ping());
    }
    
    public function testPingUnvalid()
    {
        $ping = new NetworkPing('test', 0, 'does.not.exist');
        $ping->setMethod(NetworkPing::METHOD_SOCKET);
        $this->assertFalse($ping->ping());
    }
}
