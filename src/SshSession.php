<?php

namespace PingThis;

use Ssh\SshConfigFileConfiguration;
use Ssh\Session;

class SshSession
{
    protected $session;
    
    public function __construct($configuration, $authentication = null)
    {
        $this->establishConnection($configuration, $authentication);
    }
    
    public function run($command)
    {
        return $this->session->getExec()->run($command);
    }
    
    public function getSession()
    {
        return $this->session;
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