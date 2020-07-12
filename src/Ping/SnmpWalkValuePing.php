<?php

namespace PingThis\Ping;

class SnmpWalkValuePing extends AbstractPing
{
    protected $host;
    protected $oid;
    protected $parameters;
    protected $expression;
    protected $error;

    /**
     * @param $frequency
     * @param $host           SNMP agent hostname
     * @param $oid            SNMP Object ID
     * @param $parameters     SNMP __construct and walk parameters
     * @param $expression
     */
    public function __construct(int $frequency, string $host, string $oid, array $parameters, $expression)
    {
        if (!class_exists('\SNMP')) {
            trigger_error('SnmpWalkValuePing requires PHP-SNMP extension', E_USER_ERROR);
        }

        parent::__construct($frequency);

        $this->host = $host;
        $this->oid = $oid;
        $this->expression = $expression;
        $this->parameters = array_merge(['version' => '2c', 'community' => 'public'], $parameters);
    }

    public function getName(): string
    {
        return sprintf('Check SNMP value %s at %s', $this->oid, $this->host);
    }

    public function getLastError(): ?string
    {
        return $this->error ?: 'Command failed';
    }

    public function ping(): bool
    {
        $session = $this->getSession();
        $session->valueretrieval = SNMP_VALUE_PLAIN;
        $session->exceptions_enabled = \SNMP::ERRNO_ANY;

        try {
            $response = $session->walk($this->oid);
        } catch (\SNMPException $e) {
            $this->error = $e->getMessage();
            return false;
        }

        return $this->evaluate($this->expression, [
            'response' => $response,
            'error' => &$this->error,
        ]);
    }

    protected function getSession(): \SNMP
    {
        switch ($this->parameters['version']) {
            case '1':
                return new \SNMP(\SNMP::VERSION_1, $this->host, $this->parameters['community']);
            case '2c':
                return new \SNMP(\SNMP::VERSION_2C, $this->host, $this->parameters['community']);
            case '3':
                $session = new \SNMP(\SNMP::VERSION_3, $this->host, $this->parameters['community']);
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
