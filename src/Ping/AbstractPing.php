<?php

namespace PingThis\Ping;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

abstract class AbstractPing implements PingInterface
{
    protected $frequency;
    protected $language;
    
    public function __construct($frequency)
    {
        $this->frequency = $frequency;
        $this->language = new ExpressionLanguage();
    }
    
    public function getPingFrequency()
    {
        return $this->frequency;
    }
    
    abstract public function getLastError();
    abstract public function ping();
    
    protected function evaluate($expression, $data)
    {
        return (bool) $this->language->evaluate($expression, $data);
    }
}