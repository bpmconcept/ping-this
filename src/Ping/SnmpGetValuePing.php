<?php

namespace PingThis\Ping;

class SnmpGetValuePing extends SnmpWalkValuePing
{
    /**
     * @param $frequency
     * @param $host           SNMP agent hostname
     * @param $oid            SNMP Object ID
     * @param $parameters     SNMP __construct and get parameters
     * @param $expression
     */
    public function __construct(int $frequency, string $host, string $oid, array $parameters, $expression)
    {
        if (!class_exists('\SNMP')) {
            trigger_error('SnmpGetValuePing requires PHP-SNMP extension', E_USER_ERROR);
        }

        parent::__construct($frequency, $host, $oid, $parameters, $expression);
    }

    public function ping(): bool
    {
        $session = $this->getSession();
        $session->valueretrieval = SNMP_VALUE_OBJECT | SNMP_VALUE_PLAIN;
        $session->exceptions_enabled = \SNMP::ERRNO_ANY;

        try {
            $response = $session->get($this->oid);
        } catch (\SNMPException $e) {
            $this->error = $e->getMessage();
            return false;
        }

        return $this->evaluate($this->expression, [
            'type' => $response->type,
            'value' => $response->value,
            'error' => &$this->error,
        ]);
    }
}
