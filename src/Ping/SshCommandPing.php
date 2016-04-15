<?php

namespace MarcBP\PingThis\Ping;

use Ssh\Configuration;
use Ssh\SshConfigFileConfiguration;
use Ssh\Authentication;
use Ssh\Session;

class SshCommandPing extends AbstractPing
{
    protected $command;
    protected $expected;
    protected $session;
    protected $response;
    protected $error;
    
	public function __construct($frequency, $command, $expected = null, $configuration, Authentication $authentication = null)
    {
        parent::__construct($frequency);
        
        $this->command = $command;
        $this->expected = $expected;
		
        $this->establishConnection($configuration, $authentication);
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
            $this->response = $this->session->getExec()->run($this->command);
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
    
    protected function establishConnection($configuration, $authentication)
    {
        // User has provided an hostname, build a configuration from the default config file path
        if (is_string($configuration)) {
            $configuration = new SshConfigFileConfiguration('~/.ssh/config', $configuration);
        }
        
        // Extract authentication info from the config file
        if ($authentication === null && $configuration instanceof SshConfigFileConfiguration) {
            $authentication = $configuration->getAuthentication();
        }
        
        $this->session = new Session($configuration, $authentication);
    }
}
