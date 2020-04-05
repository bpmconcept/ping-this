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
    public function __construct($frequency, $method, $uri, $expression, array $parameters = [], array $files = [], array $server = [], $content = null)
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
        $this->server = $server;
        $this->content = $content;
        $this->expression = $expression;
    }

    public function setMethod($method)
    {
        $this->method = $method;
    }

    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    public function getName()
    {
        return sprintf('HTTP %s request on %s', $this->method, $this->uri);
    }

    public function getLastError()
    {
        return $this->error;
    }

    public function ping()
    {
        try {
            list($crawler, $response) = $this->doRequest();
            $this->error = null;
        } catch (\Exception $e) {
            $this->error = sprintf('Unable to send the request, "%s"', $e->getMessage());
            return false;
        }

        $ping = $this->evaluate($this->expression, [
            'response' => $response,
            'crawler' => $crawler,
            'error' => &$this->error,
        ]);

        if (!$ping && $this->error === null) {
            $this->error = sprintf('Unvalid %s response at %s', $this->method, $this->uri);
        }

        return $ping;
    }

    protected function doRequest()
    {
        $client = HttpClient::create([
            'timeout' => 5,
            'headers' => ['User-Agent' => 'Ping-This'],
        ]);
        $browser = new HttpBrowser($client);

        $crawler = $browser->request($this->method, $this->uri, [], $this->files, $this->server, $this->content);
        $response = $browser->getResponse();

        return [$crawler, $response];
    }
}
