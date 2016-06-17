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
  
    protected function evaluate($expression, $data)
    {
        // User passed a callable
        if (is_callable($expression)) {
            $reflection = new \ReflectionFunction($expression);
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