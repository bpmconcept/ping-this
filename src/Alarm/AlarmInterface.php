<?php

namespace PingThis\Alarm;

use PingThis\Ping\PingInterface;

interface AlarmInterface
{
    /**
     * Trigger the alarm.
     * 
     * @param $ping Instance of the failing ping
     */
    public function start(PingInterface $ping);
    
    /**
     * Stop the alarm.
     * 
     * @param $ping Instance of the ping that raised the alarm
     */
    public function stop(PingInterface $ping);
}