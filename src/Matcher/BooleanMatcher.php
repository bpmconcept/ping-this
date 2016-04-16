<?php

namespace PingThis\Matcher;

class BooleanMatcher implements MatcherInterface
{
    public function match($subject)
    {
        return (bool) $subject;
    }
}