<?php

use MarcBP\PingThis\Ping\NetworkPing;
use MarcBP\PingThis\Alarm\ParallelAlarm;

class ParallelAlarmTest extends PHPUnit_Framework_TestCase
{
    public function testDispatch()
    {
        $dispatcher = new ParallelAlarm();
        
        $ping = $this->getMockBuilder('MarcBP\PingThis\Ping\AbstractPing')
            ->setConstructorArgs([0])
            ->getMock();
        
        for ($i = 1; $i <= 5; $i++) {
            $alarm = $this->getMockForAbstractClass('MarcBP\PingThis\Alarm\AbstractAlarm');
            
            $alarm->expects($this->once())
                 ->method('start')
                 ->with($ping);
            
            $alarm->expects($this->once())
                 ->method('stop')
                 ->with($ping);
            
            $dispatcher->add($alarm);
        }
        
        $this->assertCount(5, $dispatcher->getAlarms());
        $dispatcher->start($ping);
        $dispatcher->stop($ping);
    }
    
    public function testAddThenRemove()
    {
        $dispatcher = new ParallelAlarm();
        
        $alarm = $this->getMockForAbstractClass('MarcBP\PingThis\Alarm\AbstractAlarm');
        $dispatcher->add($alarm);
        $this->assertCount(1, $dispatcher->getAlarms());
        
        $dispatcher->remove($alarm);
        $this->assertCount(0, $dispatcher->getAlarms());
    }
}
