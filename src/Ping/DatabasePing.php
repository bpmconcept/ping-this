<?php

namespace PingThis\Ping;

/**
 * Try to establish a database connection using PDO.
 */
class DatabasePing extends AbstractPing
{
    protected $dsn;
    protected $username;
    protected $password;
    protected $options;
    protected $error;

    public function __construct(int $frequency, string $dsn, string $username = null, string $password = null, array $options = [])
    {
        if (!class_exists('PDO')) {
            trigger_error('DatabasePing requires PDO', E_USER_ERROR);
        }

        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->options = $options;

        parent::__construct($frequency);
    }

    public function getName(): string
    {
        return sprintf('Connect to %s', $this->dsn);
    }

    public function getLastError(): ?string
    {
        return $this->error;
    }

    public function ping(): bool
    {
        try {
            @$pdo = new \PDO($this->dsn, $this->username, $this->password, $this->options);
            return true;
        } catch (\PDOException $e) {
            $this->error = sprintf('Database %s connection error "%s"', $this->dsn, $e->getMessage());
            return false;
        }
    }
}
