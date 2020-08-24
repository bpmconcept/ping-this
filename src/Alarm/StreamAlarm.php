<?php

namespace PingThis\Alarm;

use PingThis\Ping\PingInterface;
use PingThis\Formatter\DefaultFormatter;

/**
 * Write alarms messages to a stream, like a log file or stdout.
 */
class StreamAlarm extends AbstractAlarm
{
    public function __construct($stream, bool $verbose = false)
    {
        $this->stream = $stream;
        $this->verbose = $verbose;
        $this->formatter = new DefaultFormatter();
    }

    public function start(PingInterface $ping)
    {
        $date = new \DateTime();
        $message = $this->verbose ? $this->formatter->formatFullErrorMessage($date, $ping, true) :
            $this->formatter->formatShortErrorMessage($date, $ping, true);

        fwrite($this->stream, $message . PHP_EOL);
    }

    public function stop(PingInterface $ping)
    {
        $date = new \DateTime();
        $message = $this->verbose ? $this->formatter->formatFullErrorMessage($date, $ping, false) :
            $this->formatter->formatShortErrorMessage($date, $ping, false);

        fwrite($this->stream, $message . PHP_EOL);
    }
}
