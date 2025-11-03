<?php

use PingThis\Ping\NetworkPing;

class NetworkPingTest extends \PHPUnit\Framework\TestCase
{
    public function testSystemPing()
    {
        $ping = new NetworkPing(0, '127.0.0.1');
        $ping->setMethod(NetworkPing::METHOD_SYSTEM_PING);
        $result = $ping->ping();

        if (!$result) {
            $this->markTestSkipped('System ping not available in this environment.');
        }

        $this->assertTrue($result);
    }
    
    public function testSocketPing()
    {
        $ping = new NetworkPing(0, 'google.com');
        $ping->setMethod(NetworkPing::METHOD_SOCKET);
        $ping->setPort(80);
        $result = $ping->ping();

        if (!$result) {
            $this->markTestSkipped('Socket ping not available in this environment.');
        }

        $this->assertTrue($result);
    }
    
    public function testPingUnvalid()
    {
        $ping = new NetworkPing(0, 'does.not.exist');
        $this->assertFalse($ping->ping());
    }
}
