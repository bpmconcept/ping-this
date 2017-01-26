<?php

namespace PingThis\Alarm;

use PingThis\Ping\PingInterface;

/**
 * Send an email for each alarm raised, using the PHP's mail function.
 */
class PhpEmailAlarm extends AbstractAlarm
{
    protected $email;

    public function __construct($email)
    {
        $this->email = $email;
    }

    public function start(PingInterface $ping)
    {
        $date = new \DateTime();
        $subject = $this->formatter->formatShortErrorMessage($date, $ping, true);
        $message = $this->formatter->formatFullErrorMessage($date, $ping, true);
            
        mail($this->email, sprintf('=?UTF-8?B?%s?=', base64_encode($subject)), $message);
    }

    public function stop(PingInterface $ping)
    {
        $date = new \DateTime();
        $subject = $this->formatter->formatShortErrorMessage($date, $ping, true);
        $message = $this->formatter->formatFullErrorMessage($date, $ping, true);
        
        mail($this->email, sprintf('=?UTF-8?B?%s?=', base64_encode($subject)), $message);
    }
}
