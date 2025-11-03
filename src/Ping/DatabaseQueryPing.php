<?php

namespace PingThis\Ping;

/**
 * Try to establish a connection to a database and execute a query using PDO.
 */
class DatabaseQueryPing extends AbstractPing
{
    private ?string $error = null;

    public function __construct(
        int $frequency,
        private readonly string $dsn,
        private readonly string $query,
        private readonly string|array|\Closure $expression,
        private readonly ?string $username = null,
        private readonly ?string $password = null,
        private readonly array $options = []
    )
    {
        if (!class_exists('PDO')) {
            trigger_error('DatabasePing requires PDO', E_USER_ERROR);
        }

        parent::__construct($frequency);
    }

    public function getName(): string
    {
        return sprintf('Execute "%s" on %s', $this->query, $this->dsn);
    }

    public function getLastError(): ?string
    {
        return $this->error;
    }

    public function ping(): bool
    {
        try {
            @$pdo = new \PDO($this->dsn, $this->username, $this->password, $this->options);
            $response = $pdo->query($this->query);

            $ping = $this->evaluate($this->expression, [
                'response' => $response->fetchAll(),
                'error' => &$this->error,
            ]);

            if (!$ping && $this->error === null) {
                $this->error = 'Unvalid database response';
            }

            return $ping;
        } catch (\PDOException $e) {
            $this->error = sprintf('Database %s error "%s"', $this->dsn, $e->getMessage());
            return false;
        }
    }
}
