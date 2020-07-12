<?php

namespace PingThis\Ping;

use PingThis\Ping\Base\SnmpSessionTrait;

class SnmpWalkValuePing extends AbstractPing
{
    use SnmpSessionTrait;

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
        $this->parameters = $this->getParameters($parameters);
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
}
