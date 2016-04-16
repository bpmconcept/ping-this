<?php

namespace PingThis\Alarm;

use PingThis\Ping\PingInterface;

/**
 * Send an email for each alarm raised, using the PHP's mail function.
 */
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
        @mail($this->email, $this->formatStartMessage($ping), $error);
    }

    public function stop(PingInterface $ping)
    {
        @mail($this->email, $this->formatEndMessage($ping), $error);
    }
}
