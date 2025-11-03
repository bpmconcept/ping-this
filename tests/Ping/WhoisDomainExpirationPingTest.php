<?php

use PingThis\Ping\WhoisDomainExpirationPing;

class WhoisDomainExpirationPingTest extends \PHPUnit\Framework\TestCase
{
    /** @dataProvider provideValidResponses */
    public function testCheckDateReturnsTrueWhenExpirationAboveThreshold(string $response, string $threshold): void
    {
        $ping = $this->createPing($threshold);
        $method = new \ReflectionMethod($ping, 'checkDate');
        $method->setAccessible(true);

        $error = null;
        $this->assertTrue($method->invokeArgs($ping, [$response, &$error]));
        $this->assertNull($error);
    }

    public function provideValidResponses(): array
    {
        return [
            ['Expiry Date: 2099-01-01T00:00:00Z', '-1 day'],
            ['expiration date: 2030-05-12 10:00:00', '2025-01-01'],
        ];
    }

    public function testCheckDateReturnsFalseAndSetsErrorWhenThresholdExceeded(): void
    {
        $ping = $this->createPing('+10 days');
        $method = new \ReflectionMethod($ping, 'checkDate');
        $method->setAccessible(true);

        $response = 'Expiration Date: 2020-01-01T00:00:00Z';
        $error = null;

        $this->assertFalse($method->invokeArgs($ping, [$response, &$error]));
        $this->assertSame('Domain expires on 2020-01-01 00:00:00', $ping->getLastError());
        $this->assertNull($error);
    }

    public function testCheckDateReturnsFalseWhenDateNotFound(): void
    {
        $ping = $this->createPing('+1 day');
        $method = new \ReflectionMethod($ping, 'checkDate');
        $method->setAccessible(true);

        $error = null;
        $this->assertFalse($method->invokeArgs($ping, ['No expiry listed', &$error]));
        $this->assertSame('Expiration date not found', $error);
    }

    private function createPing(string $threshold): WhoisDomainExpirationPing
    {
        return new WhoisDomainExpirationPing(1, 'example.com', 'whois.example.com', $threshold);
    }
}
