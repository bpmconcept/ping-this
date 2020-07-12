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
        switch ($this->parameters['version']) {
            case '1':
                return new \SNMP(\SNMP::VERSION_1, $this->host, $this->parameters['community']);
            case '2c':
                return new \SNMP(\SNMP::VERSION_2C, $this->host, $this->parameters['community']);
            case '3':
                $session = new \SNMP(\SNMP::VERSION_3, $this->host, $this->parameters['user']);
                $session->setSecurity($this->parameters['sec_level'],
                    $this->parameters['auth_protocol'] ?? null,
                    $this->parameters['auth_passphrase'] ?? null,
                    $this->parameters['priv_protocol'] ?? null,
                    $this->parameters['priv_passphrase'] ?? null,
                    $this->parameters['contextName'] ?? null,
                    $this->parameters['contextEngineID'] ?? null);
                return $session;
            default:
                trigger_error(sprintf('Unknown SNMP version %d', $this->parameters['version']), E_USER_ERROR);
        }
    }
}
