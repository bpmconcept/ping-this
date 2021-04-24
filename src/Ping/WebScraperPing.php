<?php

namespace PingThis\Ping;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

class WebScraperPing extends AbstractPing
{
    protected $client;
    protected $error;
    protected $method;
    protected $uri;
    protected $expression;
    protected $parameters;
    protected $files;
    protected $server;
    protected $content;

    /**
     * @param $frequency       The request frequency
     * @param $method          The request method
     * @param $uri             The URI to fetch
     * @param $expression      A user expression to check if response is valid or not
     * @param $parameters      The Request parameters
     * @param $files           The files
     * @param $server          The server parameters (HTTP headers are referenced with a HTTP_ prefix as PHP does)
     * @param $content         The raw body data
     */
    public function __construct(int $frequency, string $method, string $uri, $expression, array $parameters = [], array $files = [], array $server = [], string $content = null)
    {
        if (!class_exists('\\Symfony\\Component\\BrowserKit\\HttpBrowser')) {
            trigger_error('WebScraperPing requires "symfony/browser-kit" package installed', E_USER_ERROR);
        }

        if (!class_exists('\\Symfony\\Component\\HttpClient\\HttpClient')) {
            trigger_error('WebScraperPing requires "symfony/http-client" package installed', E_USER_ERROR);
        }

        parent::__construct($frequency);

        $this->method = $method;
        $this->uri = $uri;
        $this->expression = $expression;
        $this->parameters = $parameters;
        $this->files = $files;
        $this->server = array_merge(['HTTP_USER_AGENT' => 'PingThis'], $server);
        $this->content = $content;
        $this->expression = $expression;
    }

    public function setMethod(string $method)
    {
        $this->method = $method;
    }

    public function setUri(string $uri)
    {
        $this->uri = $uri;
    }

    public function getName(): string
    {
        return sprintf('HTTP %s request on %s', $this->method, $this->uri);
    }

    public function getLastError(): ?string
    {
        return $this->error;
    }

    public function ping(): bool
    {
        try {
            list($crawler, $response) = $this->doRequest();
            $this->error = null;

            if ($response->getHeader('content-type') == 'application/json') {
                $data = json_decode($response->getContent(), true);
            }
        } catch (\Exception $e) {
            $this->error = sprintf('Unable to send the request, "%s"', $e->getMessage());
            return false;
        }

        $ping = $this->evaluate($this->expression, [
            'response' => $response,
            'content' => $data ?? $crawler,
            'error' => &$this->error,
        ]);

        if (!$ping && $this->error === null) {
            $this->error = sprintf('Unvalid %s response at %s', $this->method, $this->uri);
        }

        return $ping;
    }

    protected function doRequest()
    {
        $client = HttpClient::create(['timeout' => 5]);
        $browser = new HttpBrowser($client);

        $crawler = $browser->request($this->method, $this->uri, [], $this->files, $this->server, $this->content);
        $response = $browser->getResponse();

        // Prevent curl resources leak
        $client->reset();

        return [$crawler, $response];
    }
}
