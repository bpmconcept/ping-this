<?php

use PingThis\Daemon;
use PingThis\Ping\AbstractPing;
use PingThis\Alarm\AbstractAlarm;
use PingThis\Alarm\StreamAlarm;

class DaemonTest extends \PHPUnit\Framework\TestCase
{
    public function testAlarmStartAndStop()
    {
        $daemon = new Daemon();

        $ping = $this->createMock(AbstractPing::class);
        $alarm = $this->createMock(AbstractAlarm::class);

        $daemon->registerAlarm($alarm);
        $daemon->registerPing($ping);

        $ping->setMaxAttemptsBeforeAlarm(3);

        // Simulate a falsy ping
        $ping->expects($this->any())
             ->method('ping')
             ->will($this->onConsecutiveCalls(true, false, true, false, false, true, false, false, false, true));

        // The alarm should be notified twice: 1 start, then 1 stop
        $alarm->expects($this->exactly(3))
             ->method('start')
             ->with($ping);

        $alarm->expects($this->exactly(3))
             ->method('stop')
             ->with($ping);

        foreach (range(1, 10) as $i) {
            $daemon->runOnce();
        }
    }
}
