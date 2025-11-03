<?php

namespace PingThis\Ping;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

abstract class AbstractPing implements PingInterface
{
    protected int $attempts;
    protected ExpressionLanguage $language;
    protected static int $defaultAttempts = 3;

    public function __construct(protected readonly int $frequency)
    {
        $this->attempts = self::$defaultAttempts;
        $this->language = new ExpressionLanguage();
    }

    public function getPingFrequency(): int
    {
        return $this->frequency;
    }

    public function setMaxAttemptsBeforeAlarm(int $attempts)
    {
        $this->attempts = $attempts;
    }

    public function getMaxAttemptsBeforeAlarm(): int
    {
        return $this->attempts;
    }

    public static function setDefaultMaxAttemptsBeforeAlarm(int $attempts): void
    {
        if ($attempts < 1) {
            throw new \InvalidArgumentException('Default attempts must be greater than or equal to 1.');
        }

        self::$defaultAttempts = $attempts;
    }

    public static function getDefaultMaxAttemptsBeforeAlarm(): int
    {
        return self::$defaultAttempts;
    }

    protected function evaluate($expression, $data)
    {
        // User passed a callable
        if (is_callable($expression)) {
            $reflection = is_array($expression)
                ? new \ReflectionMethod($expression[0], $expression[1])
                : new \ReflectionFunction($expression);
            $parameters = $reflection->getNumberOfParameters();

            // User has provided a callable with too many parameters
            if ($parameters > count($data)) {
                throw new \InvalidArgumentException(sprintf('A callable with %d parameters at most was expected', count($data)));
            }

            return (bool) $expression(...$this->buildArguments($data, $parameters));
        }

        // User passed a string, we assume that it is an expression for ExpressionLanguage
        if (is_string($expression)) {
            return (bool) $this->language->evaluate($expression, $data);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function buildArguments(array &$data, int $parameters): array
    {
        $arguments = [];
        $index = 0;
        foreach ($data as $key => &$value) {
            $arguments[] =& $data[$key];
            if (++$index >= $parameters) {
                break;
            }
        }
        unset($value);

        return $arguments;
    }
}
