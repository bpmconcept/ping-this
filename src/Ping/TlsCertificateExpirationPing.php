<?php

namespace PingThis\Ping;

/**
 * Check the expiration date of a web server's certificate. This ping needs
 * the openssl module to be installed.
 *
 * @todo Use ExpressionLanguage for date comparison and more
 */
class TlsCertificateExpirationPing extends AbstractPing
{
	protected $socket;
    protected $threshold;
    protected $date;
    
	public function __construct($frequency, $socket, $threshold)
    {
        if (!function_exists('openssl_x509_parse')) {
			trigger_error('TlsCertificateExpirationPing requires the OpenSSL module', E_USER_ERROR);
		}
        
        parent::__construct($frequency);
        
		$this->socket = $socket;
        $this->threshold = $threshold;
    }
    
	public function setSocket($socket)
	{
		$this->socket = $socket;
	}

    public function setThreshold($threshold)
    {
        $this->threshold = $threshold;
    }
    
    public function getName()
    {
        return sprintf('Certificate validity on %s', $this->socket);
    }
    
    public function getLastError()
	{
		return sprintf('Certificate expires on %s', $this->date->format('Y-m-d H:i:s'));
	}

    public function ping()
	{
        $this->date = $this->getCertificateExpirationDate($this->socket);
        
		return $this->date > new \DateTime($this->threshold);
	}
    
    protected function getCertificateExpirationDate($socket)
    {
        $timeout = min(10, $this->getPingFrequency());
        $context = stream_context_create(['ssl' => ['capture_peer_cert' => TRUE]]);
        $read = stream_socket_client($socket, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);
        $certificate = stream_context_get_params($read);
        $infos = openssl_x509_parse($certificate['options']['ssl']['peer_certificate']);
        
        return new \DateTime('@'.$infos['validTo_time_t']);
    }
}
