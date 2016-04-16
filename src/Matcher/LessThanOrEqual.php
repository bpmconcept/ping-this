<?php

namespace PingThis\Matcher;

class LessThanOrEqual implements MatcherInterface
{
    protected $value;
    
    public function __construct($value)
    {
        $this->value = $value;
    }
    
    public function match($subject)
    {
        return $subject <= $this->value;
    }
}