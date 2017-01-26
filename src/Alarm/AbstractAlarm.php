<?php

namespace PingThis\Alarm;

use PingThis\Formatter\FormatterInterface;
use PingThis\Ping\PingInterface;
use PingThis\Formatter\DefaultFormatter;

abstract class AbstractAlarm implements AlarmInterface
{
    protected $formatter;
    
    abstract public function start(PingInterface $ping);
    abstract public function stop(PingInterface $ping);

    public function setFormatter(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }
}
