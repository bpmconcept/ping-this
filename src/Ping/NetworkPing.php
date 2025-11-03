<?php

namespace PingThis\Ping;

/**
 * Check if a given host responds to a standard network ping by emitting a raw
 * ICMP echo request (type 8) and waiting for a matching echo reply (type 0).
 * This implementation avoids third-party dependencies and talks directly with
 * the network stack through raw sockets.
 */
class NetworkPing extends AbstractPing
{
    protected int $ttl = 64;
    protected int $timeout = 3;
    protected ?float $latency = null;
    protected ?string $lastError = null;

    public function __construct(int $frequency, private readonly string $host)
    {
        parent::__construct($frequency);
    }

    public function setTtl(int $ttl): void
    {
        $this->ttl = $ttl;
    }

    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    public function getName(): string
    {
        return sprintf('Ping request on %s', $this->host);
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function ping(): bool
    {
        $this->lastError = null;

        $ip = $this->resolveIPv4Address($this->host);
        if ($ip === null) {
            $this->lastError = sprintf('Unable to resolve host "%s".', $this->host);

            return false;
        }

        $socket = $this->createRawSocket();
        if ($socket === null) {
            // $lastError set by createRawSocket()
            return false;
        }

        try {
            $ipLevel = $this->getIpSocketLevel();
            if (!$this->configureTtl($socket, $ipLevel)) {
                return false;
            }

            if (!@socket_set_option($socket, \SOL_SOCKET, \SO_RCVTIMEO, ['sec' => $this->timeout, 'usec' => 0])) {
                $this->lastError = sprintf('Unable to set receive timeout (%d s): %s', $this->timeout, $this->describeSocketError($socket));

                return false;
            }

            try {
                $identifier = random_int(0, 0xffff);
                $sequence = random_int(0, 0xffff);
            } catch (\Throwable) {
                $identifier = mt_rand(0, 0xffff);
                $sequence = mt_rand(0, 0xffff);
            }
            $payload = $this->buildPayload();
            $packet = $this->buildPacket($identifier, $sequence, $payload);

            $start = microtime(true);
            $bytesSent = @socket_sendto($socket, $packet, strlen($packet), 0, $ip, 0);

            if ($bytesSent === false || $bytesSent !== strlen($packet)) {
                $this->lastError = sprintf('Failed to send ICMP packet: %s', $this->describeSocketError($socket));

                return false;
            }

            $buffer = '';
            $from = '';
            $port = 0;
            $bytesReceived = @socket_recvfrom($socket, $buffer, 512, 0, $from, $port);

            if ($bytesReceived === false) {
                $this->lastError = sprintf('No ICMP response received: %s', $this->describeSocketError($socket));

                return false;
            }

            if (!$this->isMatchingEchoReply($buffer, $identifier, $sequence)) {
                $this->lastError = sprintf('Invalid ICMP echo reply received from %s.', $from ?: $ip);

                return false;
            }

            $this->latency = (microtime(true) - $start) * 1000;

            return true;
        } finally {
            socket_close($socket);
        }
    }

    private function buildPayload(): string
    {
        // Include a timestamp for latency computation and pad to a small size.
        try {
            $padding = random_bytes(16);
        } catch (\Throwable) {
            $padding = str_repeat("\0", 16);
        }

        return pack('d', microtime(true)) . $padding;
    }

    private function buildPacket(int $identifier, int $sequence, string $payload): string
    {
        $header = pack('C2n3', 8, 0, 0, $identifier, $sequence);
        $checksum = $this->checksum($header . $payload);

        return pack('C2n3', 8, 0, $checksum, $identifier, $sequence) . $payload;
    }

    private function checksum(string $data): int
    {
        $length = strlen($data);
        $sum = 0;

        for ($i = 0; $i < $length; $i += 2) {
            $firstByte = ord($data[$i]);
            $secondByte = $i + 1 < $length ? ord($data[$i + 1]) : 0;
            $sum += ($firstByte << 8) + $secondByte;
            $sum = ($sum & 0xffff) + ($sum >> 16);
        }

        return (~$sum) & 0xffff;
    }

    private function isMatchingEchoReply(string $buffer, int $identifier, int $sequence): bool
    {
        if ($buffer === '') {
            return false;
        }

        $firstByte = ord($buffer[0]);
        $headerLength = ($firstByte & 0x0f) << 2; // IHL * 4 bytes

        if (strlen($buffer) < $headerLength + 8) {
            return false;
        }

        $icmpHeader = substr($buffer, $headerLength, 8);
        $parts = unpack('Ctype/Ccode/nchecksum/nidentifier/nsequence', $icmpHeader);

        if ($parts === false) {
            return false;
        }

        return $parts['type'] === 0
            && $parts['code'] === 0
            && $parts['identifier'] === $identifier
            && $parts['sequence'] === $sequence;
    }

    protected function createRawSocket(): ?\Socket
    {
        $protocol = \defined('SOL_ICMP') ? \SOL_ICMP : @getprotobyname('icmp');
        if ($protocol === false) {
            $this->lastError = 'ICMP protocol is not available on this system.';

            return null;
        }

        $socket = @socket_create(\AF_INET, \SOCK_RAW, $protocol);
        if ($socket === false) {
            $this->lastError = sprintf('Unable to create raw socket. Root privileges may be required: %s', socket_strerror(socket_last_error()));

            return null;
        }

        return $socket;
    }

    private function resolveIPv4Address(string $host): ?string
    {
        if (filter_var($host, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4)) {
            return $host;
        }

        $resolved = @gethostbyname($host);

        if ($resolved === $host && !filter_var($host, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4)) {
            return null;
        }

        return $resolved;
    }

    private function describeSocketError(\Socket $socket): string
    {
        $code = socket_last_error($socket);

        return sprintf('[%d] %s', $code, socket_strerror($code));
    }

    /**
     * Some PHP builds may not expose SOL_IP, fall back to IPPROTO_IP or 0.
     */
    private function getIpSocketLevel(): int
    {
        if (\defined('SOL_IP')) {
            return \SOL_IP;
        }

        if (\defined('IPPROTO_IP')) {
            return \IPPROTO_IP;
        }

        return 0;
    }

    /**
     * Resolve the TTL socket option constant, defaulting to 4 when missing.
     */
    private function getTtlSocketOption(): int
    {
        if (\defined('IP_TTL')) {
            return \IP_TTL;
        }

        if (\defined('IPPROTO_IP')) {
            // TTL option value typically matches IPPROTO_IP for raw sockets.
            return \IPPROTO_IP;
        }

        return 4; // IPPROTO_IP constant value in most implementations.
    }

    private function configureTtl(\Socket $socket, int $ipLevel): bool
    {
        $ttlOption = $this->getTtlSocketOption();

        if (@socket_set_option($socket, $ipLevel, $ttlOption, $this->ttl)) {
            return true;
        }

        $errorCode = socket_last_error($socket);

        if (in_array($errorCode, $this->getIgnorableTtlErrorCodes(), true)) {
            socket_clear_error($socket);

            return true;
        }

        $this->lastError = sprintf('Unable to set TTL (%d): %s', $this->ttl, $this->describeSocketError($socket));

        return false;
    }

    /**
     * Some platforms signal TTL unsupported via ENOPROTOOPT / EOPNOTSUPP.
     */
    private function getIgnorableTtlErrorCodes(): array
    {
        $codes = [];

        if (\defined('SOCKET_ENOPROTOOPT')) {
            $codes[] = \SOCKET_ENOPROTOOPT;
        } else {
            $codes[] = 92;
        }

        if (\defined('SOCKET_EOPNOTSUPP')) {
            $codes[] = \SOCKET_EOPNOTSUPP;
        } else {
            $codes[] = 95;
        }

        return array_unique($codes);
    }
}
