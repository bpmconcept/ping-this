<?php

namespace PingThis\Alarm;

use PingThis\Ping\PingInterface;
use PingThis\Formatter\DefaultFormatter;

/**
 * Write alarm messages to a log file.
 */
class LogFileAlarm extends StreamAlarm
{
    public function __construct($file)
    {
        $stream = fopen($file, 'r+');

        if (false === (flock($stream, LOCK_EX | LOCK_NB))) {
            trigger_error(sprintf('Cannot open %s, file is already in used', $file), E_USER_ERROR);
        }

        parent::__construct($stream);
    }
}
