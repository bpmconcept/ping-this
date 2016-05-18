<?php

namespace PingThis\Ping;

use Goutte\Client;

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
        parent::__construct($frequency);
        
        $this->method = $method;
        $this->uri = $uri;
        $this->expression = $expression;
        $this->parameters = $parameters;
        $this->files = $files;
        $this->server = $server;
        $this->content = $content;
        $this->expression = $expression;
        $this->client = new Client();
    }

    public function setMethod($method)
	{
		$this->method = $method;
	}
    
	public function setUri($uri)
	{
		$this->uri = $uri;
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
        $crawler = $this->client->request($this->method, $this->uri, $this->parameters, $this->files, $this->server, $this->content);
        $response = $this->client->getResponse();
        
        return [$crawler, $response];
    }
}
