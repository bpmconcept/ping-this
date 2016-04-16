<?php

namespace PingThis\Formatter;

use PingThis\Ping\PingInterface;

interface FormatterInterface
{
    /**
     * Format an error message that will be forwarded to the end user thanks
     * to the alarm.
     *
     * @param $date         Date of the event
     * @param $ping         Instance of the ping that raised the alarm
     * @param $newAlarm     Boolean indicating if it is the begin or the end
     *                      of an alarm
     */
    public function formatErrorMessage(\DateTime $date, PingInterface $ping, $newAlarm);
}
