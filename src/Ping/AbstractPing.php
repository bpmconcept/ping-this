<?php

namespace PingThis\Ping;

abstract class AbstractPing implements PingInterface
{
    protected $frequency;
    
    public function __construct($frequency)
    {
        $this->frequency = $frequency;
    }
    
    public function getPingFrequency()
    {
        return $this->frequency;
    }
    
    abstract public function getLastError();
    abstract public function ping();
}