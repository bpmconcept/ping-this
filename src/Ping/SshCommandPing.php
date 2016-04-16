<?php

namespace PingThis\Ping;

use PingThis\SshSession;
use PingThis\Matcher\MatcherInterface;

class SshCommandPing extends AbstractPing
{
    protected $session;
    protected $command;
    protected $matcher;
    protected $response;
    protected $code;
    protected $error;
    
	public function __construct($frequency, SshSession $session, $command, MatcherInterface $matcher = null)
    {
        parent::__construct($frequency);
        
        $this->session = $session;
        $this->command = $command;
        $this->matcher = $matcher;
    }

	public function setCommand($command)
	{
		$this->command = $command;
	}
    
    public function setMatcher($matcher)
	{
		$this->matcher = $matcher;
	}

    public function getLastError()
	{
        return $this->error;
	}

    public function ping()
	{
		try {
            $this->response = $this->session->run($this->command);
            $this->code = 0;
        } catch (\RuntimeException $e) {
            $this->code = $e->getCode();
        }
        
        // No matcher provided: check the response code
        if ($this->matcher === null) {
            if ($this->code !== 0) {
                $this->error = sprintf('Incorrect response code %d from the server', $this->code);
                return false;
            }
            
            return true;
        }
        
        // Check the server response
        if (!$this->matcher->match($this->response)) {
            $this->error = sprintf('Incorrect response from the server: "%s"', $this->response);
            return false;
        }
        
        return true;
	}
}
