<?php

namespace MarcBP\PingThis\Ping;

abstract class AbstractPing implements PingInterface
{
    protected $name;
    protected $frequency;
    
    public function __construct($name, $frequency)
    {
        $this->name = $name;
        $this->frequency = $frequency;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getPingFrequency()
    {
        return $this->frequency;
    }
    
    abstract function getLastError();
    abstract function ping();
}