<?php

namespace PingThis\Ping;

class TcpCommandPing extends AbstractPing
{
    protected $address;
    protected $port;
    protected $command;
    protected $expression;
    protected $error;
    
    /**
     * @param $frequency                  
     * @param $address        IP address to connect to
     * @param $port           TCP port to use
     * @param $command        Command to send once connection is established
     * @param $expression     A conditional expression respecting Symfony's ExpressionLanguage syntax.
     *                        User has access to 1 variable: response.
     */
    public function __construct($frequency, $address, $port, $command = null, $expression = null)
    {
        parent::__construct($frequency);
        
        $this->address = $address;
        $this->port = $port;
        $this->command = $command;
        $this->expression = $expression;
    }

    public function setCommand($command)
    {
        $this->command = $command;
    }
    
    public function getName()
    {
        return sprintf('TCP request on %s:%d', $this->address, $this->port);
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
        if (!$socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) {
            $this->error = socket_strerror(socket_last_error());
            return false;
        }
        
        if (!$result = @socket_connect($socket, gethostbyname($this->address), $this->port)) {
            $this->error = sprintf('socket_connect failed: "%s"', socket_strerror(socket_last_error()));
            return false;
        }
        
        // No command provided, test only connection success
        if (!$this->command) {
            @socket_close($socket);
            return true;
        }
        
        $response = '';
        socket_write($socket, $this->command, strlen($this->command));
        
        while (($out = socket_read($socket, 2048)) !== "") {
            $response .= $out;
        }

        return $this->evaluate($this->expression, [
            'response' => $response,
        ]);
    }
}
