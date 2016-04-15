<?php

namespace MarcBP\PingThis\Formatter;

use MarcBP\PingThis\Ping\PingInterface;

class DefaultFormatter implements FormatterInterface
{
    public function formatErrorMessage(\DateTime $date, PingInterface $ping, $newAlarm)
    {
        if ($newAlarm && $error = $ping->getLastError()) {
            $subject = $error;
        } else {
            $subject = $newAlarm ? 'alarm triggered' : 'end of alarm';
        }

        return sprintf('[%s] %s', $date->format('Y-m-d H:i:s'), $subject);
    }
}
