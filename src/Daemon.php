<?php

namespace PingThis;

use PingThis\Ping\PingInterface;
use PingThis\Alarm\AlarmInterface;
use PingThis\StatusListener\StatusListenerInterface;

class Daemon
{
    protected $debug;
    protected $alarm;
    protected $pings = [];
    protected $listeners = [];
    
    public function __construct()
    {
        $this->debug = false;
        $this->lastCheck = new \SplObjectStorage();
        $this->inErrorState = new \SplObjectStorage();
    }
    
    public function enableDebugMode($debug)
    {
        $this->debug = $debug;
    }

    public function registerPing(PingInterface $ping)
    {
        $this->pings[] = new PingStatus($ping);
    }

    public function registerAlarm(AlarmInterface $alarm)
    {
        $this->alarm = $alarm;
    }
    
    public function registerStatusListener(StatusListenerInterface $listener)
    {
        $this->listeners[] = $listener;
    }
    
    public function runOnce()
    {
        foreach ($this->pings as $pingStatus) {
            if ((time() - $pingStatus->getLastCheck()) >= $pingStatus->getPing()->getPingFrequency()) {
                $pingStatus->setLastCheck(time());
                
                $attempts = 1;
                
                do {
                    // Check if it correctly pings
                    $this->log(sprintf('Checking "%s"... ', $pingStatus->getPing()->getName()));
                    $test = $pingStatus->getPing()->ping();
                    $this->log($test ? "\033[32mOK\033[0m\n" : "\033[31mError\033[0m\n");
                } while (!$test && $attempts++ < $pingStatus->getPing()->getMaxAttemptsBeforeAlarm());
                                
                // This ping triggers an error
                if (!$test) {
                    if ($pingStatus->getStatus()) {
                        $pingStatus->setStatus(false);
                        $this->alarm->start($pingStatus->getPing());
                    }
                }

                // This ping instance was in error state
                elseif (!$pingStatus->getStatus()) {
                    $pingStatus->setStatus(true);
                    $this->alarm->stop($pingStatus->getPing());
                }
            }
        }
        
        foreach ($this->listeners as $listener) {
            $listener->update($this->pings);
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
