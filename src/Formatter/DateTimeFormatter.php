<?php

namespace PingThis\Formatter;

use PingThis\Ping\PingInterface;

class DateTimeFormatter extends DefaultFormatter
{
    protected $format;
    
    public function __construct($format = 'Y-m-d H:i:s')
    {
        $this->format = $format;
    }
    
    public function formatShortErrorMessage(\DateTime $date, PingInterface $ping, $newAlarm)
    {
        return $date->format($this->format) . ' - ' . parent::formatShortErrorMessage($date, $ping, $newAlarm);
    }
    
    public function formatFullErrorMessage(\DateTime $date, PingInterface $ping, $newAlarm)
    {
        return parent::formatFullErrorMessage($date, $ping, $newAlarm);
    }
}
