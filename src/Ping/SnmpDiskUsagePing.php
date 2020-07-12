<?php

namespace PingThis\Ping;

use PingThis\Ping\Base\SnmpSessionTrait;

class SnmpDiskUsagePing extends AbstractPing
{
    use SnmpSessionTrait;

    protected $host;
    protected $parameters;
    protected $threshold;
    protected $error;

    /**
     * @param $frequency
     * @param $host           SNMP agent hostname
     * @param $parameters     SNMP __construct and walk parameters
     * @param $threshold      Threshold alert (between 0 and 100)
     */
    public function __construct(int $frequency, string $host, array $parameters, float $threshold)
    {
        if (!class_exists('\SNMP')) {
            trigger_error('SnmpDiskUsagePing requires PHP-SNMP extension', E_USER_ERROR);
        }

        parent::__construct($frequency);

        $this->host = $host;
        $this->threshold = $threshold;
        $this->parameters = $this->getParameters($parameters);
    }

    public function getName(): string
    {
        return sprintf('Check disk usage via SNMP on %s', $this->host);
    }

    public function getLastError(): ?string
    {
        return $this->error ?: 'Command failed';
    }

    public function ping(): bool
    {
        $session = $this->getSession();
        $session->valueretrieval = SNMP_VALUE_OBJECT | SNMP_VALUE_PLAIN;
        $session->exceptions_enabled = \SNMP::ERRNO_ANY;
        $error = [];

        try {
            foreach ($session->walk("HOST-RESOURCES-MIB::hrStorageIndex") as $index) {
                if ($session->get("HOST-RESOURCES-MIB::hrStorageType.$index->value")->value === 'HOST-RESOURCES-MIB::hrStorageTypes.4') {
                    $path = $session->get("HOST-RESOURCES-MIB::hrStorageDescr.$index->value")->value;
                    $size = $session->get("HOST-RESOURCES-MIB::hrStorageSize.$index->value")->value;
                    $used = $session->get("HOST-RESOURCES-MIB::hrStorageUsed.$index->value")->value;
                    $ratio = $size > 0 ? 100 * $used / $size : 0;

                    if ($ratio > $this->threshold) {
                        $error[] = sprintf('Disk usage on "%s" has reached %.1f%%', $path, $ratio);
                    }
                }
            }
        } catch (\SNMPException $e) {
            $this->error = $e->getMessage();
            return false;
        }

        if (count($error) > 0) {
            $this->error = implode("\n", $error);
            return false;
        }

        return true;
    }
}
