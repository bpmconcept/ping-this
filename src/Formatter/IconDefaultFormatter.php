<?php

namespace PingThis\Formatter;

use PingThis\Ping\PingInterface;

class IconDefaultFormatter extends DefaultFormatter
{
    public function formatShortErrorMessage(\DateTime $date, PingInterface $ping, $newAlarm)
    {
        $icon = $newAlarm ? '"\uD83D\uDD34"' : '"\uD83D\uDD35"';
        return json_decode($icon) . ' ' . parent::formatShortErrorMessage($date, $ping, $newAlarm);
    }
    
    public function formatFullErrorMessage(\DateTime $date, PingInterface $ping, $newAlarm)
    {
        return parent::formatFullErrorMessage($date, $ping, $newAlarm);
    }
}
