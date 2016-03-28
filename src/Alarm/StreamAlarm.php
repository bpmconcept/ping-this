<?php

namespace MarcBP\PingThis\Alarm;

use MarcBP\PingThis\Ping\PingInterface;

/**
 * Write alarms messages to a stream, like a log file or stdout.
 */
class StreamAlarm extends AbstractAlarm
{
    public function __construct($stream)
    {
        $this->stream = $stream;
    }

    public function start(PingInterface $ping)
    {
        fwrite($this->stream, $this->formatStartMessage($ping) . PHP_EOL);
    }

    public function stop(PingInterface $ping)
    {
        fwrite($this->stream, $this->formatEndMessage($ping) . PHP_EOL);
    }
}
