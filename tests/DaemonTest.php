<?php

use PingThis\Daemon;
use PingThis\Group;
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

    public function testGroupsFlat()
    {
        $daemon = new Daemon();

        $ping1 = $this->createMock(AbstractPing::class);
        $ping2 = $this->createMock(AbstractPing::class);
        $ping3 = $this->createMock(AbstractPing::class);

        $daemon->registerGroup(new Group('group1', $ping1));
        $daemon->registerGroup(new Group('group2', $ping2));
        $daemon->registerGroup(new Group('group3', $ping3));

        $ping1->expects($this->once())->method('ping');
        $ping2->expects($this->once())->method('ping');
        $ping3->expects($this->once())->method('ping');

        $daemon->runOnce();
    }

    public function testGroupsRecursive()
    {
        $daemon = new Daemon();

        $ping1 = $this->createMock(AbstractPing::class);
        $ping2 = $this->createMock(AbstractPing::class);
        $ping3 = $this->createMock(AbstractPing::class);

        $group1 = new Group('group1', $ping1);
        $group2 = new Group('group2', $group1, $ping2);
        $group3 = new Group('group3', $group2, $ping3);

        $daemon->registerGroup($group3);

        $ping1->expects($this->once())->method('ping');
        $ping2->expects($this->once())->method('ping');
        $ping3->expects($this->once())->method('ping');

        $daemon->runOnce();
    }
}
