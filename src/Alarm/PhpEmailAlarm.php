<?php

namespace PingThis\Alarm;

use PingThis\Ping\PingInterface;

/**
 * Send an email for each alarm raised, using the PHP's mail function.
 */
class PhpEmailAlarm extends AbstractAlarm
{
    protected $email;
    protected $headers;
    
    public function __construct($email, array $headers = [])
    {
        $this->email = $email;
        $this->headers = $headers;
    }

    public function start(PingInterface $ping)
    {
        $date = new \DateTime();
        $subject = $this->formatter->formatShortErrorMessage($date, $ping, true);
        $message = $this->formatter->formatFullErrorMessage($date, $ping, true);
        
        mail($this->email, sprintf('=?UTF-8?B?%s?=', base64_encode($subject)), $message, $this->getHeaders());
    }

    public function stop(PingInterface $ping)
    {
        $date = new \DateTime();
        $subject = $this->formatter->formatShortErrorMessage($date, $ping, false);
        $message = $this->formatter->formatFullErrorMessage($date, $ping, false);
        
        mail($this->email, sprintf('=?UTF-8?B?%s?=', base64_encode($subject)), $message, $this->getHeaders());
    }
    
    protected function getHeaders()
    {
        $headers = "";
        
        foreach (array_merge(['From' => 'PingThis'], $this->headers) as $k => $v) {
            $headers .= "$k: $v\r\n";
        }
        
        return $headers;
    }
}
