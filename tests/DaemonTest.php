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
            ->disableOriginalConstructor()
            ->setMethods(['ping'])
            ->getMockForAbstractClass();
        
        
        $alarm = $this->getMockForAbstractClass('PingThis\Alarm\AbstractAlarm');
        
        $daemon->registerAlarm($alarm);
        $daemon->registerPing($ping);
        
        $ping->setMaxAttemptsBeforeAlarm(3);
        
        // Simulate a falsy ping
        $ping->expects($this->any())
             ->method('ping')
             ->will($this->onConsecutiveCalls(true, false, true, false, false, true, false, false, false, true));
        
        // The alarm should be notified twice: 1 start, then 1 stop
        $alarm->expects($this->exactly(2))
             ->method('start')
             ->with($ping);
        
        $alarm->expects($this->once())
             ->method('stop')
             ->with($ping);
        
        foreach (range(1, 10) as $i) {
            $daemon->runOnce();
        }
    }
}
