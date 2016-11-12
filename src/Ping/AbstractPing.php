<?php

namespace PingThis\Ping;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

abstract class AbstractPing implements PingInterface
{
    protected $frequency;
    protected $attempts;
    protected $language;
    
    public function __construct($frequency)
    {
        $this->frequency = $frequency;
        $this->attempts = 3;
        $this->language = new ExpressionLanguage();
    }
    
    public function getPingFrequency()
    {
        return $this->frequency;
    }
    
    public function setMaxAttemptsBeforeAlarm($attempts)
    {
        $this->attempts = $attempts;
    }
    
    public function getMaxAttemptsBeforeAlarm()
    {
        return $this->attempts;
    }
  
    protected function evaluate($expression, $data)
    {
        // User passed a callable
        if (is_callable($expression)) {
            $reflection = is_array($expression) ? new \ReflectionMethod($expression[0], $expression[1]) : new \ReflectionFunction($expression);
            $parameters = $reflection->getNumberOfParameters();
            
            // Use has provided a callable with too much parameters
            if ($parameters > count($data)) {
                throw new \InvalidArgumentException(sprintf('A callable with %d parameters at most was expected', count($data)));
            }
            
            return (bool) call_user_func_array($expression, array_slice($data, 0, $parameters));
        }
        
        // User passed a string, we assume that it is an expression for ExpressionLanguage
        if (is_string($expression)) {
            return (bool) $this->language->evaluate($expression, $data);
        }
    }
}