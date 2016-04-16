<?php

use PingThis\Daemon;
use PingThis\Ping\AbstractPing;
use PingThis\Alarm\StreamAlarm;

class DaemonTest extends PHPUnit_Framework_TestCase
{
    public function testAlarmStartAndStop()
    {
        $daemon = new Daemon();
        
        $ping = $this->getMockBuilder('PingThis\Ping\AbstractPing')
            ->setConstructorArgs([0])
            ->getMock();
        
        $alarm = $this->getMockForAbstractClass('PingThis\Alarm\AbstractAlarm');
        
        $daemon->registerAlarm($alarm);
        $daemon->registerPing($ping);
        
        // Simulate a false ping
        $ping->expects($this->any())
             ->method('ping')
             ->will($this->onConsecutiveCalls(true, false, true));
        
        // The alarm should be notified twice: 1 start, then 1 stop
        $alarm->expects($this->once())
             ->method('start')
             ->with($ping);
        
        $alarm->expects($this->once())
             ->method('stop')
             ->with($ping);
        
        $daemon->runOnce();
        $daemon->runOnce();
        $daemon->runOnce();
    }
}
