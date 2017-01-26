<?php

namespace PingThis\Formatter;

use PingThis\Ping\PingInterface;

interface FormatterInterface
{
    /**
     * Format a short error message so that the user can identify quickly what is going on.
     *
     * @param $date         Date of the event
     * @param $ping         Instance of the ping that raised the alarm
     * @param $newAlarm     Boolean indicating if it is the begin or the end
     *                      of an alarm
     */
    public function formatShortErrorMessage(\DateTime $date, PingInterface $ping, $newAlarm);
    
    /**
     * Format a long and descriptive error message.
     *
     * @param $date         Date of the event
     * @param $ping         Instance of the ping that raised the alarm
     * @param $newAlarm     Boolean indicating if it is the begin or the end
     *                      of an alarm
     */
    public function formatFullErrorMessage(\DateTime $date, PingInterface $ping, $newAlarm);
}
