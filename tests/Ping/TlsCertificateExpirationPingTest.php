<?php

use PingThis\Ping\TlsCertificateExpirationPing;

class TlsCertificateExpirationPingTest extends \PHPUnit\Framework\TestCase
{
    public function testExpirationDate()
    {
        $ping = $this->getMockBuilder('PingThis\Ping\TlsCertificateExpirationPing')
            ->setConstructorArgs([1, 'www.test.com', 443, TlsCertificateExpirationPing::IMPLICIT_TLS, '+1 day'])
            ->onlyMethods(['getCertificateExpirationDate', 'createSocket', 'initialize', 'startTls'])
            ->getMock();

        $socket = fopen('php://memory', 'r');

        $ping->method('createSocket')->willReturn($socket);
        $ping->method('initialize')->willReturnCallback(static function (): void {
        });
        $ping->method('startTls')->willReturnCallback(static function (): void {
        });

        $ping->expects($this->any())
             ->method('getCertificateExpirationDate')
             ->willReturnOnConsecutiveCalls(
                new \DateTime('+ 7 days'),
                new \DateTime('- 1 days'),
                new \DateTime('+ 2 days'),
                new \DateTime('+ 6 hours')
             );

        $this->assertTrue($ping->ping());
        $this->assertFalse($ping->ping());
        $this->assertTrue($ping->ping());
        $this->assertFalse($ping->ping());
    }
}
