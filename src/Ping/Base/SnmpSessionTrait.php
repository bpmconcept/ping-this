<?php

namespace PingThis\Ping\Base;

trait SnmpSessionTrait
{
    protected function getParameters($parameters): array
    {
        return array_merge(['version' => '2c', 'community' => 'public'], $parameters);
    }

    protected function getSession(): \SNMP
    {
        set_error_handler(function($errno, $errstr, $errfile, $errline) {
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });

        switch ($this->parameters['version']) {
            case '1':
                $snmp = new \SNMP(\SNMP::VERSION_1, $this->host, $this->parameters['community']);
                break;
            case '2c':
                $snmp = new \SNMP(\SNMP::VERSION_2C, $this->host, $this->parameters['community']);
                break;
            case '3':
                $session = new \SNMP(\SNMP::VERSION_3, $this->host, $this->parameters['user']);
                $session->setSecurity($this->parameters['sec_level'],
                    $this->parameters['auth_protocol'] ?? null,
                    $this->parameters['auth_passphrase'] ?? null,
                    $this->parameters['priv_protocol'] ?? null,
                    $this->parameters['priv_passphrase'] ?? null,
                    $this->parameters['contextName'] ?? null,
                    $this->parameters['contextEngineID'] ?? null);
                $snmp = $session;
                break;
            default:
                restore_error_handler();
                trigger_error(sprintf('Unknown SNMP version %d', $this->parameters['version']), E_USER_ERROR);
        }

        // Enable exceptions instead of warnings
        $snmp->exceptions_enabled = \SNMP::ERRNO_ANY;
        restore_error_handler();

        return $snmp;
    }
}
