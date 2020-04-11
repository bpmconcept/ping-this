<?php

use PingThis\Ping\NetworkPing;
use PingThis\Alarm\LogFileAlarm;

class LogFileAlarmTest extends \PHPUnit\Framework\TestCase
{
    public function testLock()
    {
        $file = tempnam(sys_get_temp_dir(), 'alarm');
        $dispatcher = new LogFileAlarm($file);

        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        $dispatcher = new LogFileAlarm($file);
    }
}
