<?php

use PingThis\Alarm\LogFileAlarm;

class LogFileAlarmTest extends \PHPUnit\Framework\TestCase
{
    public function testLock(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'alarm');
        $dispatcher = new LogFileAlarm($file);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Cannot open %s, file is already in used', $file));

        set_error_handler(static function ($severity, $message): bool {
            throw new \RuntimeException($message);
        });

        try {
            new LogFileAlarm($file);
        } finally {
            restore_error_handler();
        }
    }
}
