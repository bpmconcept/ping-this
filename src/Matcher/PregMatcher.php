<?php

namespace PingThis\Matcher;

class PregMatcher implements MatcherInterface
{
    protected $regex;
    
    public function __construct($regex)
    {
        $this->regex = $regex;
    }
    
    public function match($subject)
    {
        return preg_match($this->regex, $subject) === 1;
    }
}