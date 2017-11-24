<?php

namespace PingThis\Ping;

class StreamSocketCommandPing extends AbstractPing
{
    protected $address;
    protected $timeout;
    protected $command;
    protected $expression;
    protected $error;
    
    /**
     * @param $frequency                  
     * @param $socket         Socket address (see stream_socket_client)
     * @param $command        Command to send once connection is established
     * @param $expression     A conditional expression respecting Symfony's ExpressionLanguage syntax.
     *                        User has access to 1 variable: response.
     * @param $timeout        Connection timeout
     */
    public function __construct($frequency, $socket, $command = null, $expression = null, $timeout = 3)
    {
        parent::__construct($frequency);
        
        $this->socket = $socket;
        $this->command = $command;
        $this->timeout = $timeout;
        $this->expression = $expression;
    }

    public function setCommand($command)
    {
        $this->command = $command;
    }
    
    public function getName()
    {
        return sprintf('Stream socket request on %s', $this->socket);
    }

    public function getLastError()
    {
        if (null !== $this->error) {
            return $this->error;
        } else {
            return 'Command failed';
        }
    }

    public function ping()
    {
        if (!$stream = @stream_socket_client($this->socket, $errno, $errstr, $this->timeout)) {
            $this->error = sprintf('Stream socket connection failed: "%s"', $errstr);
            return false;
        }
        
        // No command provided, test only connection success
        if (!$this->command) {
            fclose($stream);
            return true;
        }
        
        fwrite($stream, $this->command);
        $response = stream_get_contents($stream);
        fclose($stream);

        return $this->evaluate($this->expression, [
            'response' => $response,
        ]);
    }
}
