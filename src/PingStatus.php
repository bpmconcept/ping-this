<?php

namespace PingThis;

use PingThis\Ping\PingInterface;

class PingStatus
{
    protected $ping;
    protected $lastCheck = 0;
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
    
    public function setLastCheck($date)
    {
        return $this->lastCheck = $date;
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
