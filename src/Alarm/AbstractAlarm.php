<?php

namespace MarcBP\PingThis\Alarm;

use MarcBP\PingThis\Ping\PingInterface;

abstract class AbstractAlarm implements AlarmInterface
{
    abstract public function start(PingInterface $ping);
    abstract public function stop(PingInterface $ping);
}