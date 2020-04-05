<?php

namespace PingThis\Ping;

class LdapSearchPing extends AbstractPing
{
    protected $url;
    protected $dn;
    protected $query;
    protected $expression;
    protected $rdn;
    protected $password;
    protected $options;
    protected $error;

    public function __construct(int $frequency, string $url, string $dn = null, string $query = null, $expression = null, string $rdn = null, string $password = null, array $options = [])
    {
        if (!function_exists('ldap_connect')) {
            trigger_error('LdapSearchPing requires PHP-LDAP extension', E_USER_ERROR);
        }

        $this->url = $url;
        $this->dn = $dn;
        $this->query = $query;
        $this->expression = $expression;
        $this->rdn = $rdn;
        $this->password = $password;
        $this->options = $options + [LDAP_OPT_NETWORK_TIMEOUT => 3, LDAP_OPT_PROTOCOL_VERSION => 3];

        parent::__construct($frequency);
    }

    public function getName(): string
    {
        return sprintf('LDAP search on %s', $this->url);
    }

    public function getLastError(): string
    {
        return $this->error;
    }

    public function ping(): bool
    {
        if (false === ($ldap = @ldap_connect($this->url))) {
            $this->error = 'Failed to create a connection';
            return false;
        }

        foreach ($this->options as $key => $value) {
            ldap_set_option($ldap, $key, $value);
        }

        // Authentication
        if (!@ldap_bind($ldap, $this->rdn, $this->password)) {
            $this->error = sprintf('Unable to bind to server: %s', ldap_error($ldap));
            ldap_close($ldap);
            return false;
        }

        // No command provided, test only connection success
        if (!$this->query) {
            ldap_close($ldap);
            return true;
        }

        // Execute search query
        if (false === ($query = @ldap_search($ldap, $this->dn, $this->query))) {
            $this->error = sprintf('Query failed: %s', ldap_error($ldap));
            ldap_close($ldap);
            return false;
        }

        $response = ldap_get_entries($ldap, $query);
        ldap_close($ldap);

        return $this->evaluate($this->expression, [
            'response' => $response,
            'error' => &$this->error,
        ]);
    }
}
