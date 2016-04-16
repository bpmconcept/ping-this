<?php

namespace PingThis\Matcher;

class LessThan implements MatcherInterface
{
    protected $value;
    
    public function __construct($value)
    {
        $this->value = $value;
    }
    
    public function match($subject)
    {
        return $subject < $this->value;
    }
}