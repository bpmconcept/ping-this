<?php

namespace PingThis\Formatter;

use PingThis\Ping\PingInterface;

class DefaultFormatter implements FormatterInterface
{
    public function formatShortErrorMessage(\DateTime $date, PingInterface $ping, $newAlarm)
    {
        if ($newAlarm) {
            return sprintf("%s failed", $ping->getName());
        }
        
        else {
            return sprintf("%s is working again", $ping->getName());
        }
    }
    
    public function formatFullErrorMessage(\DateTime $date, PingInterface $ping, $newAlarm)
    {
        if (!$newAlarm) {
            return "";
        }
        
        return $ping->getLastError();
    }
}
