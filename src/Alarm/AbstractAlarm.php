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

    protected function formatStartMessage(PingInterface $ping, \DateTime $date = null)
    {
        if (!$date) {
            $date = new \DateTime();
        }

        return $this->formatter->formatErrorMessage($date, $ping, true);
    }

    protected function formatEndMessage(PingInterface $ping, \DateTime $date = null)
    {
        if (!$date) {
            $date = new \DateTime();
        }

        return $this->formatter->formatErrorMessage($date, $ping, false);
    }
}
