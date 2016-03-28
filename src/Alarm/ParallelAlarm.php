<?php

namespace MarcBP\PingThis\Alarm;

use MarcBP\PingThis\Ping\PingInterface;

/**
 * Dispatch the alert on multiple other Alarm instances.
 */
class ParallelAlarm extends AbstractAlarm
{
    protected $alarms;

    public function __construct()
    {
        $this->alarms = new \SplObjectStorage();
    }
    
    public function getAlarms()
    {
        return $this->alarms;
    }
    
    public function add(AlarmInterface $alarm)
    {
        $this->alarms->attach($alarm);
    }
    
    public function remove(AlarmInterface $alarm)
    {
        $this->alarms->detach($alarm);
    }

    public function start(PingInterface $ping)
    {
        foreach ($this->alarms as $alarm) {
            $alarm->start($ping);
        }
    }

    public function stop(PingInterface $ping)
    {
        foreach ($this->alarms as $alarm) {
            $alarm->stop($ping);
        }
    }
}