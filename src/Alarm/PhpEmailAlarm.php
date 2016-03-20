<?php

namespace MarcBP\PingThis\Alarm;

use MarcBP\PingThis\Ping\PingInterface;

class PhpEmailAlarm extends AbstractAlarm
{
    protected $email;
    protected $subject;
    
    public function __construct($email)
    {
        $this->email = $email;
    }
    
    public function start(PingInterface $ping)
    {
        @mail($this->email, $this->formatEmailSubject(true, $ping), $error);
    }
    
    public function stop(PingInterface $ping)
    {
        @mail($this->email, $this->formatEmailSubject(false, $ping), $error);
    }
    
    protected function formatEmailSubject($isStarting, PingInterface $ping)
    {
        return sprintf('[%s] %s', $ping->getName(), $isStarting ? 'alarm triggered' : 'end of alert');
    }
}