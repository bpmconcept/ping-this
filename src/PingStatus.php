<?php

namespace PingThis;

use PingThis\Ping\PingInterface;

class PingStatus
{
    protected $ping;
    protected $lastCheck = 0;
    protected $nextCheck = 0;
    protected $status = true;

    public function __construct(PingInterface $ping)
    {
        $this->ping = $ping;
    }

    public function getPing()
    {
        return $this->ping;
    }

    public function getLastCheck()
    {
        return $this->lastCheck;
    }

    public function hasBeenCheckedAt($date, $randomization = false)
    {
        $frequency = $this->ping->getPingFrequency();
        $offset = $randomization ? random_int(-$frequency/2, $frequency/2) : 0;
        $this->lastCheck = $date;
        $this->nextCheck = $date + $frequency + $offset;
    }

    public function needsCheck($date)
    {
        return $date >= $this->nextCheck;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        return $this->status = $status;
    }
}
