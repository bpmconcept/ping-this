<?php

namespace PingThis;

use PingThis\Ping\PingInterface;
use PingThis\Alarm\AlarmInterface;
use PingThis\Formatter\FormatterInterface;
use PingThis\Formatter\DefaultFormatter;

class Daemon
{
    protected $debug;
    protected $alarm;
    protected $pings = [];
    protected $lastCheck;
    protected $inErrorState;

    public function __construct()
    {
        $this->debug = false;
        $this->lastCheck = new \SplObjectStorage();
        $this->inErrorState = new \SplObjectStorage();
        $this->formatter = new DefaultFormatter();
    }
    
    public function enableDebugMode($debug)
    {
        $this->debug = $debug;
    }

    public function registerPing(PingInterface $ping)
    {
        $this->pings[] = $ping;
        $this->lastCheck[$ping] = 0;
    }

    public function registerAlarm(AlarmInterface $alarm)
    {
        $this->alarm = $alarm;
        $this->alarm->setFormatter($this->formatter);
    }

    public function setFormatter(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
        $this->alarm->setFormatter($this->formatter);
    }
    
    public function runOnce()
    {
        foreach ($this->pings as $ping) {
            if ((time() - $this->lastCheck[$ping]) >= $ping->getPingFrequency()) {
                $this->lastCheck[$ping] = time();
                
                $attempts = 1;
                
                do {
                    // Check if it correctly pings
                    $this->log(sprintf('Checking "%s"... ', $ping->getName()));
                    $test = $ping->ping();
                    $this->log($test ? "\033[32mOK\033[0m\n" : "\033[31mError\033[0m\n");
                } while (!$test && $attempts++ < $ping->getMaxAttemptsBeforeAlarm());
                                
                // This ping triggers an error
                if (!$test) {
                    if (!$this->inErrorState->contains($ping)) {
                        $this->inErrorState->attach($ping);
                        $this->alarm->start($ping);
                    }
                }

                // This ping instance was in error state
                elseif ($this->inErrorState->contains($ping)) {
                    $this->inErrorState->detach($ping);
                    $this->alarm->stop($ping);
                }
            }
        }
    }

    public function run()
    {
        while (1) {
            $this->runOnce();
            sleep(1);
        }
    }
    
    protected function log($message)
    {
        if ($this->debug) {
            printf($message);
        }
    }
}
