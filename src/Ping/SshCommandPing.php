<?php

namespace PingThis\Ping;

use PingThis\SshSession;

class SshCommandPing extends AbstractPing
{
    protected $session;
    protected $command;
    protected $expression;
    protected $stdout;
    protected $stderr;
    protected $code;
    protected $error;
    
    /**
     * @param $frequency                         
     * @param SshSession $session    A shared SSH connection
     * @param $command               Command to execute on the remote host
     * @param $expression            A conditional expression respecting Symfony's ExpressionLanguage syntax.
     *                               User has access to 3 variables: stdout, stderr and status.
     */
    public function __construct($frequency, SshSession $session, $command, $expression = 'status == 0')
    {
        parent::__construct($frequency);
        
        $this->session = $session;
        $this->command = $command;
        $this->expression = $expression;
    }

    public function setCommand($command)
    {
        $this->command = $command;
    }
    
    public function getName()
    {
        return sprintf('Command %s on %s', $this->command, $this->session->getSession()->getConfiguration()->getHost());
    }

    public function getLastError()
    {
        if (null !== $this->error) {
            return $this->error;
        } elseif ($this->status === 0) {
            return sprintf('Command returned "%s"', $this->stdout);
        } elseif (!$this->stderr) {
            return sprintf('Command exited with error %d', $this->status);
        } else {
            return sprintf('Command exited with error %d, "%s"', $this->status, is_array($this->stderr) ? implode("\n", $this->stderr) : $this->stderr);
        }            
    }

    public function ping()
    {
        try {
            $this->error = null;
            $this->stdout = $this->normalize($this->session->run($this->command));
            $this->status = 0;
        } catch (\RuntimeException $e) {
            $this->status = $e->getCode();
            $this->stderr = $this->normalize($e->getMessage());
        }

        return $this->evaluate($this->expression, [
            'stdout' => $this->stdout,
            'stderr' => $this->stderr,
            'status' => $this->status,
            'error' => &$this->error,
        ]);
    }
    
    protected function normalize($response)
    {
        // Split the strings into an array
        $array = explode("\n", $response);
        
        // Remove last line if empty
        if (end($array) === "") {
            array_pop($array);
        }
        
        // Flatten array of 1 element
        return count($array) === 1 ? $array[0] : $array;
    }
}
