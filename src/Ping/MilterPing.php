<?php

namespace PingThis\Ping;

class MilterPing extends AbstractPing
{
    protected $host;
    protected $port;
    protected $headers;
    protected $body;
    protected $expression;
    protected $error;

    const COMMAND_MACRO = 'D';
    const COMMAND_CONNECT = 'C';
    const COMMAND_OPTNEG = 'O';
    const COMMAND_HELO = 'H';
    const COMMAND_MAIL = 'M';
    const COMMAND_RCPT = 'R';
    const COMMAND_HEADER = 'L';
    const COMMAND_EOH = 'N';
    const COMMAND_BODY = 'B';
    const COMMAND_BODYEOB = 'E';
    const COMMAND_QUIT = 'Q';

    const RESPONSE_ADDRCPT = '+';
    const RESPONSE_DELRCPT = '-';
    const RESPONSE_ACCEPT = 'a';
    const RESPONSE_REPLBODY = 'b';
    const RESPONSE_CONTINUE = 'c';
    const RESPONSE_DISCARD = 'd';
    const RESPONSE_CONN_FAIL = 'f';
    const RESPONSE_ADDHEADER = 'h';
    const RESPONSE_INSHEADER = 'i';
    const RESPONSE_CHGHEADER = 'm';
    const RESPONSE_PROGRESS = 'p';
    const RESPONSE_QUARANTINE = 'q';
    const RESPONSE_REJECT = 'r';
    const RESPONSE_SETSENDER = 's';
    const RESPONSE_TEMPFAIL = 't';
    const RESPONSE_REPLYCODE = 'y';

    const ACTIONS_ADDHDRS = 0x01;
    const ACTIONS_CHGBODY = 0x02;
    const ACTIONS_ADDRCPT = 0x04;
    const ACTIONS_DELRCPT = 0x08;
    const ACTIONS_CHGHDRS = 0x10;
    const ACTIONS_QUARANTINE = 0x20;

    const PROTOCOL_NOCONNECT = 0x01;
    const PROTOCOL_NOHELO = 0x02;
    const PROTOCOL_NOMAIL = 0x04;
    const PROTOCOL_NORCPT = 0x08;
    const PROTOCOL_NOBODY = 0x10;
    const PROTOCOL_NOHDRS = 0x20;
    const PROTOCOL_NOEOH = 0x40;

    /**
     * @param $frequency
     * @param $host         Milter server hostname or IP
     * @param $port         Milter server port
     * @param $headers      Array of mail headers
     * @param $body         Mail body
     */
    public function __construct(int $frequency, string $host, int $port, array $headers, string $body, $expression)
    {
        parent::__construct($frequency);

        $this->host = $host;
        $this->port = $port;
        $this->headers = $headers;
        $this->body = $body;
        $this->expression = $expression;
    }

    public function getName(): string
    {
        return sprintf('Check Milter at %s:%d', $this->host, $this->port);
    }

    public function getLastError(): string
    {
        if (null !== $this->error) {
            return $this->error;
        } else {
            return 'Milter failed';
        }
    }

    public function ping(): bool
    {
        if (!$stream = @stream_socket_client(sprintf('tcp://%s:%d', $this->host, $this->port), $errno, $errstr, 3)) {
            $this->error = sprintf('Stream socket connection failed: "%s"', $errstr);
            return false;
        }

        stream_set_timeout($stream, 30);

        $this->writeCommand($stream, self::COMMAND_OPTNEG, pack('NNN', 2, 0x3f, 0x7f));
        $response = $this->readResponse($stream);
        $negotiate = unpack('Nversion/Nactions/Nprotocol', substr($response, 1));

        if (!($negotiate['protocol'] & self::PROTOCOL_NOCONNECT)) {
            $this->writeCommand($stream, self::COMMAND_CONNECT, pack('a*xava*x', 'localhost', '4', 25, '127.0.0.1'));
            if (($response = $this->readResponse($stream)) !== self::RESPONSE_CONTINUE) {
                $this->error = 'Unvalid Milter Connect response: ' . $this->logResponse($response);
                return false;
            }
        }

        if (!($negotiate['protocol'] & self::PROTOCOL_NOHELO)) {
            $this->writeCommand($stream, self::COMMAND_HELO, pack('a*x', 'localhost'));
            if (($response = $this->readResponse($stream)) !== self::RESPONSE_CONTINUE) {
                $this->error = 'Unvalid Milter HELO response: ' . $this->logResponse($response);
                return false;
            }
        }

        if (!($negotiate['protocol'] & self::PROTOCOL_NOMAIL)) {
            $this->writeCommand($stream, self::COMMAND_MAIL, pack('a*x', $this->headers['From']));
            if (($response = $this->readResponse($stream)) !== self::RESPONSE_CONTINUE) {
                $this->error = 'Unvalid Milter Mail From response: ' . $this->logResponse($response);
                return false;
            }
        }

        if (!($negotiate['protocol'] & self::PROTOCOL_NORCPT)) {
            $this->writeCommand($stream, self::COMMAND_RCPT, pack('a*x', $this->headers['To']));
            if (($response = $this->readResponse($stream)) !== self::RESPONSE_CONTINUE) {
                $this->error = 'Unvalid Milter Recipient response: ' . $this->logResponse($response);
                return false;
            }
        }

        if (!($negotiate['protocol'] & self::PROTOCOL_NOHDRS)) {
            foreach ($this->headers as $key => $value) {
                $this->writeCommand($stream, self::COMMAND_HEADER, pack('a*xa*x', $key, $value));
                if (($response = $this->readResponse($stream)) !== self::RESPONSE_CONTINUE) {
                    $this->error = 'Unvalid Milter Header response: ' . $this->logResponse($response);
                    return false;
                }
            }

            if (!($negotiate['protocol'] & self::PROTOCOL_NOEOH)) {
                $this->writeCommand($stream, self::COMMAND_EOH);
                if (($response = $this->readResponse($stream)) !== self::RESPONSE_CONTINUE) {
                    $this->error = 'Unvalid Milter EOH response: ' . $this->logResponse($response);
                    return false;
                }
            }
        }

        if (!($negotiate['protocol'] & self::PROTOCOL_NOBODY)) {
            $this->writeCommand($stream, self::COMMAND_BODY, $this->body);
            if (($response = $this->readResponse($stream)) !== self::RESPONSE_CONTINUE) {
                $this->error = 'Unvalid Milter Body response: ' . $this->logResponse($response);
                return false;
            }
        }

        $continueResponses = [
            self::RESPONSE_ADDHEADER,
            self::RESPONSE_CHGHEADER,
            self::RESPONSE_INSHEADER,
            self::RESPONSE_ADDRCPT,
            self::RESPONSE_DELRCPT,
            self::RESPONSE_SETSENDER,
            self::RESPONSE_PROGRESS,
        ];

        do {
            $this->writeCommand($stream, self::COMMAND_BODYEOB);
            $response = $this->readResponse($stream);
            list($command, $data) = $this->parseResponse($response);
            $actions[] = ['command' => $command, 'data' => $data];
        } while (in_array($command, $continueResponses));

        $this->writeCommand($stream, self::COMMAND_QUIT);
        fclose($stream);

        return $this->evaluate($this->expression, [
            'actions' => $actions,
            'error' => &$this->error,
        ]);
    }

    private function writeCommand($stream, $command, string $payload = '')
    {
        fwrite($stream, pack('N', strlen($payload) + 1) . $command . $payload);
    }

    private function readResponse($stream)
    {
        if (false === ($response = fread($stream, 4)) || strlen($response) !== 4) {
            return false;
        }

        $parsed = unpack('Nsize', $response);

        return fread($stream, $parsed['size']);
    }

    private function parseResponse($response)
    {
        switch ($response[0]) {
            case self::RESPONSE_ADDHEADER:
                $data['name'] = trim(substr($response, 1, strpos($response, "\x00")));
                $data['value'] = trim(substr($response, 1 + strlen($data['name'])));
                break;
        }

        return [$response[0], $data ?? null];
    }

    private function getResponseCommand($command)
    {
        switch ($command) {
            case self::RESPONSE_ADDRCPT:
                return ['code' => 'ADDRCPT'];
            case self::RESPONSE_DELRCPT:
                return ['code' => 'DELRCPT'];
            case self::RESPONSE_ACCEPT:
                return ['code' => 'ACCEPT'];
            case self::RESPONSE_REPLBODY:
                return ['code' => 'REPLBODY'];
            case self::RESPONSE_CONTINUE:
                return ['code' => 'CONTINUE'];
            case self::RESPONSE_DISCARD:
                return ['code' => 'DISCARD'];
            case self::RESPONSE_CONN_FAIL:
                return ['code' => 'CONN_FAIL'];
            case self::RESPONSE_ADDHEADER:
                return ['code' => 'ADDHEADER'];
            case self::RESPONSE_INSHEADER:
                return ['code' => 'INSHEADER'];
            case self::RESPONSE_CHGHEADER:
                return ['code' => 'CHGHEADER'];
            case self::RESPONSE_PROGRESS:
                return ['code' => 'PROGRESS'];
            case self::RESPONSE_QUARANTINE:
                return ['code' => 'QUARANTINE'];
            case self::RESPONSE_REJECT:
                return ['code' => 'REJECT'];
            case self::RESPONSE_SETSENDER:
                return ['code' => 'SETSENDER'];
            case self::RESPONSE_TEMPFAIL:
                return ['code' => 'TEMPFAIL'];
            case self::RESPONSE_REPLYCODE:
                return ['code' => 'REPLYCODE'];
            default:
                return ['code' => 'UNKNOWN'];
        }
    }

    private function logResponse($response)
    {
        $log = sprintf('Command=%s', $this->getResponseCommand($response[0])['code']);

        if (strlen($response) !== 1) {
            $log .= sprintf(' Response=%s', bin2hex($response));
        }

        return $log;
    }
}
