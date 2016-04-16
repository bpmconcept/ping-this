<?php

namespace PingThis\Ping;

use PingThis\SshSession;

class SshCommandPing extends AbstractPing
{
    protected $session;
    protected $command;
    protected $expected;
    protected $response;
    protected $error;
    
	public function __construct($frequency, SshSession $session, $command, $expected = null)
    {
        parent::__construct($frequency);
        
        $this->session = $session;
        $this->command = $command;
        $this->expected = $expected;
    }

	public function setCommand($command)
	{
		$this->command = $command;
	}
    
    public function setExpected($expected)
	{
		$this->expected = $expected;
	}

    public function getLastError()
	{
        return $this->error;
	}

    public function ping()
	{
		try {
            $this->response = $this->session->run($this->command);
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
        
        // Check the server response
        if (false == ($check = $this->expected === null ? $this->response : preg_match($this->expected, $this->response))) {
            $this->error = sprintf('Incorrect response from the server: "%s"', $this->response);
            return false;
        }
        
        return true;
	}
}
