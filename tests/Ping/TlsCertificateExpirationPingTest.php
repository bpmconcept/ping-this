<?php

use MarcBP\PingThis\Ping\TlsCertificateExpirationPing;

class TlsCertificateExpirationPingTest extends PHPUnit_Framework_TestCase
{
    public function testExpirationDate()
    {
        $ping = $this->getMockBuilder('MarcBP\PingThis\Ping\TlsCertificateExpirationPing')
            ->setConstructorArgs(['test', 1, 'ssl://www.test.com:443', '+1 day'])
            ->setMethods(['getCertificateExpirationDate'])
            ->getMock();

        $ping->expects($this->any())
             ->method('getCertificateExpirationDate')
             ->will($this->onConsecutiveCalls(
                new \DateTime('+ 7 days'),
                new \DateTime('- 1 days'),
                new \DateTime('+ 2 days'),
                new \DateTime('+ 6 hours')
             ));
        
        $this->assertTrue($ping->ping());
        $this->assertFalse($ping->ping());
        $this->assertTrue($ping->ping());
        $this->assertFalse($ping->ping());
    }
}
