<?php

use PingThis\Daemon;
use PingThis\Matcher\GreaterThanOrEqual;
use PingThis\Ping\SshCommandPing;

class SshCommandPingTest extends PHPUnit_Framework_TestCase
{
    public function testResponseContent()
    {
        $session = $this->getMockBuilder('PingThis\SshSession')
            ->disableOriginalConstructor()
            ->setMethods(['run'])
            ->getMock();
        
        $session->expects($this->any())
            ->method('run')
            ->with('command')
            ->will($this->onConsecutiveCalls('41', '42', '43', '40'));
        
        $ping = new SshCommandPing(1, $session, 'command', new GreaterThanOrEqual(42));

        $this->assertFalse($ping->ping());
        $this->assertTrue($ping->ping());
        $this->assertTrue($ping->ping());
        $this->assertFalse($ping->ping());
    }
    
    public function testResponseCode()
    {
        $session = $this->getMockBuilder('PingThis\SshSession')
            ->disableOriginalConstructor()
            ->setMethods(['run'])
            ->getMock();
        
        $session->expects($this->at(0))
            ->method('run')
            ->with('command')
            ->will($this->returnValue('ok'));
        
        $session->expects($this->at(1))
            ->method('run')
            ->with('command')
            ->will($this->throwException(new \RunTimeException('error', 1)));
        
        $ping = new SshCommandPing(1, $session, 'command');

        $this->assertTrue($ping->ping());
        $this->assertFalse($ping->ping());
    }
}
