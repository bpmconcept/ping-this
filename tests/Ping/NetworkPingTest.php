<?php

use PingThis\Ping\NetworkPing;

class NetworkPingTest extends \PHPUnit\Framework\TestCase
{
    public function testPingFailsWhenHostCannotBeResolved(): void
    {
        $ping = new NetworkPing(0, 'does.not.exist');

        $this->assertFalse($ping->ping());
        $this->assertSame('Unable to resolve host "does.not.exist".', $ping->getLastError());
    }

    public function testBuildPacketComputesChecksum(): void
    {
        $ping = new NetworkPing(0, '127.0.0.1');
        $payload = 'test-payload';

        $buildPacket = new \ReflectionMethod(NetworkPing::class, 'buildPacket');
        $buildPacket->setAccessible(true);
        $packet = $buildPacket->invoke($ping, 0x1337, 0x0001, $payload);

        $this->assertSame(8 + strlen($payload), strlen($packet));

        $header = substr($packet, 0, 8);
        $unpacked = unpack('Ctype/Ccode/nchecksum/nidentifier/nsequence', $header);

        $this->assertNotFalse($unpacked);
        $this->assertSame(8, $unpacked['type']);
        $this->assertSame(0, $unpacked['code']);
        $this->assertSame(0x1337, $unpacked['identifier']);
        $this->assertSame(0x0001, $unpacked['sequence']);

        $checksumMethod = new \ReflectionMethod(NetworkPing::class, 'checksum');
        $checksumMethod->setAccessible(true);
        $expectedChecksum = $checksumMethod->invoke(
            $ping,
            pack('C2n3', 8, 0, 0, 0x1337, 0x0001) . $payload
        );

        $this->assertSame($expectedChecksum, $unpacked['checksum']);
    }

    public function testIsMatchingEchoReplyAcceptsValidReply(): void
    {
        $ping = new NetworkPing(0, '127.0.0.1');
        $identifier = 0x2222;
        $sequence = 0x0101;
        $payload = 'icmp-response';

        $checksumMethod = new \ReflectionMethod(NetworkPing::class, 'checksum');
        $checksumMethod->setAccessible(true);
        $icmpHeader = pack('C2n3', 0, 0, 0, $identifier, $sequence);
        $checksum = $checksumMethod->invoke($ping, $icmpHeader . $payload);
        $icmpHeader = pack('C2n3', 0, 0, $checksum, $identifier, $sequence);

        $ipHeader = $this->buildIpv4Header(strlen($icmpHeader) + strlen($payload));
        $buffer = $ipHeader . $icmpHeader . $payload;

        $isMatching = new \ReflectionMethod(NetworkPing::class, 'isMatchingEchoReply');
        $isMatching->setAccessible(true);

        $this->assertTrue($isMatching->invoke($ping, $buffer, $identifier, $sequence));
    }

    public function testIsMatchingEchoReplyRejectsMismatchedReply(): void
    {
        $ping = new NetworkPing(0, '127.0.0.1');
        $identifier = 0x2222;
        $sequence = 0x0101;
        $payload = 'icmp-response';

        $checksumMethod = new \ReflectionMethod(NetworkPing::class, 'checksum');
        $checksumMethod->setAccessible(true);
        $icmpHeader = pack('C2n3', 0, 0, 0, $identifier, $sequence);
        $checksum = $checksumMethod->invoke($ping, $icmpHeader . $payload);
        $icmpHeader = pack('C2n3', 0, 0, $checksum, $identifier + 1, $sequence);

        $ipHeader = $this->buildIpv4Header(strlen($icmpHeader) + strlen($payload));
        $buffer = $ipHeader . $icmpHeader . $payload;

        $isMatching = new \ReflectionMethod(NetworkPing::class, 'isMatchingEchoReply');
        $isMatching->setAccessible(true);

        $this->assertFalse($isMatching->invoke($ping, $buffer, $identifier, $sequence));
    }

    /**
     * Build a minimal IPv4 header with the correct IHL for testing purposes.
     */
    private function buildIpv4Header(int $payloadLength): string
    {
        $version = 4;
        $ihl = 5;
        $verIhl = ($version << 4) + $ihl;
        $typeOfService = 0;
        $totalLength = 20 + $payloadLength;
        $identification = 0;
        $flagsFragmentOffset = 0;
        $ttl = 64;
        $protocol = 1; // ICMP
        $headerChecksum = 0;
        $source = ip2long('192.0.2.1');
        $destination = ip2long('203.0.113.1');

        return pack(
            'CCnnnCCnNN',
            $verIhl,
            $typeOfService,
            $totalLength,
            $identification,
            $flagsFragmentOffset,
            $ttl,
            $protocol,
            $headerChecksum,
            $source,
            $destination
        );
    }
}
