<?php

namespace MarcBP\PingThis\Ping;

/**
 * Basically check (through headers only) if a web server answers correctly to
 * a given HTTP request.
 */
class HttpHeaderPing extends AbstractPing
{
	protected $address;
    protected $error;
    protected $expectedCode;
    protected $checks = [];

	public function __construct($frequency, $address, $code = 200, $headers = [])
    {
        parent::__construct($frequency);
        
		$this->address = $address;
        $this->expectedCode = $code;
        $this->checks = $this->getLowercaseKeys($headers);
    }
    
    public function setExpectedResponseCode($code)
    {
        $this->expectedCode = $code;
    }
    
    public function setExpectedHeader($header, $regexp)
    {
        $this->checks[strtolower($header)] = $regexp;
    }

	public function setAddress($address)
	{
		$this->address = $address;
	}

    public function getLastError()
	{
		return sprintf('Page %s does not respond correctly (%s)', $this->address, $this->error);
	}

    public function ping()
	{
		$headers = $this->getLowercaseKeys($this->getHeaders($this->address));
        $code = intval(substr($headers[0], 9, 3));
        
        if ($code !== $this->expectedCode) {
            $this->error = sprintf('HTTP response code %d', $code);
            return false;
        }
        
        foreach ($this->checks as $header => $regexp) {
            // Is this header present ?
            if (!array_key_exists($header, $this->checks)) {
                $this->error = sprintf('header %s absent from the response', $header);
                return false;
            }
            
            // Is this header incorrect ?
            if (!preg_match($regexp, $headers[$header])) {
                $this->error = sprintf('%s: %s', $header, $headers[$header]);
                return false;
            }
        }
        
        return true;
	}
    
    protected function getHeaders($address)
    {
        return get_headers($address, 1);
    }
    
    protected function getLowercaseKeys(array $array)
    {
        $result = [];
        
        foreach ($array as $key => $value) {
            $result[strtolower($key)] = $value;
        }
        
        return $result;
    }
}
