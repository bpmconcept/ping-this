# PingThis

[![CI](https://github.com/bpmconcept/ping-this/actions/workflows/php.yml/badge.svg)](https://github.com/bpmconcept/ping-this/actions/workflows/php.yml)

PingThis is a lightweight PHP 8.3+ toolkit that lets you compose headless monitoring checks and route actionable alerts with just a bit of code.

## Example

``` php
use PingThis\Daemon;
use PingThis\Alarm\PhpEmailAlarm;
use PingThis\Ping\NetworkPing;
use PingThis\Ping\WebScraperPing;
use PingThis\Ping\DatabasePing;
use PingThis\Ping\TlsCertificateExpirationPing;

$daemon = new Daemon();

// Check if the host correctly answers to ping every 10 seconds
$daemon->registerPing(new NetworkPing(10, 'domain.com'));

// Check if a webserver responds correctly to a HTTP request every 30 seconds
$daemon->registerPing(new WebScraperPing(30, 'GET', 'https://domain.com', 'response.getStatusCode() == 200'));
$daemon->registerPing(new WebScraperPing(30, 'GET', 'https://domain.com', 'content.filter(".css").count()'));

// Or equivalently using any PHP callable
$daemon->registerPing(new WebScraperPing(30, 'GET', 'https://domain.com', function ($response, $content) {
    return $response->getStatus() < 400 && $content->filter('.element')->text() === "Hello";
}));

// Check every day that a certificate won't expire during the next week
$daemon->registerPing(new TlsCertificateExpirationPing(86400, 'domain.com', 443, TlsCertificateExpirationPing::IMPLICIT_TLS, '+7 days'));

// Check if a remote SQL server is still up every 10 seconds
$daemon->registerPing(new DatabasePing(10, 'mysql:host=my.sql.server', 'login', 'password'));

// Otherwise send an email to alert an admin
$daemon->registerAlarm(new PhpEmailAlarm('your@email.com'));

$daemon->run();
```

## Quick description

PingThis aims to provide a simple and effective way for monitoring whatever you want.
Configure a daemon with one Alarm and one or multiple Pings. The Daemon periodically
verifies each Ping and, in case of failing, triggers the Alarm. Any class could act
like an Alarm or a Ping, provided that it implements respectively the `AlarmInterface`
or the `PingInterface`.

The different built-in Pings rely on Symfony's [Expression Language Component](https://symfony.com/doc/current/components/expression_language.html)
to allow a quick and easy construction of triggering logic but can be equivalently replaced
by a PHP callable.

## Built-in Pings

### Network

Name                            | Description
:------------------------------ | :---------------------------------------------------------------------------------------
NetworkPing                     | Sends a standard ICMP ping and checks the ICMP response
StreamSocketCommandPing         | Sends a custom payload through a TCP/UDP/Unix socket and checks the response
TlsCertificateExpirationPing    | Initiates a TLS handshake and checks the expiration date of a certificate
SnmpGetValuePing                | Gets SNMP object value from a remote SNMP agent
SnmpWalkValuePing               | Gets SNMP object values from a remote SNMP agent
SnmpDiskUsagePing               | Walks SNMP storage metrics and checks disk usage against a threshold

### Web

Name                            | Description
:------------------------------ | :---------------------------------------------------------------------------------------
HttpPing                        | Sends a HTTP request and checks only the returned code
WebScraperPing                  | Sends a HTTP request and get back a [Response](https://symfony.com/doc/current/components/browser_kit.html), along with a [Crawler](https://symfony.com/doc/current/components/dom_crawler.html) instance
WhoisDomainExpirationPing       | Queries WHOIS to ensure a domain expiration date stays beyond a threshold

### Mails

Name                            | Description
:------------------------------ | :---------------------------------------------------------------------------------------
ImapServerPing                  | Connects to a IMAP server and checks the welcome response
SmtpServerPing                  | Connects to a SMTP server and checks the welcome response
MilterPing                      | Simulates an SMTP conversation with a Milter server and validates its actions

### Services

Name                            | Description
:------------------------------ | :---------------------------------------------------------------------------------------
DatabasePing                    | Establishes a connection to a database using PDO
DatabaseQueryPing               | Executes a SQL query on a database using PDO
LdapSearchPing                  | Executes a query on a LDAP server and checks the response

### Built-in Alarms

Name            | Description
:-------------- | :---------------------------------------------------------------------------------------
PhpEmailAlarm   | Send an email, using the PHP's mail() function
StreamAlarm     | Logs the alarms events to a given open stream (stdout or file for instance)
LogAlarm        | Specialized StreamAlarm for files that adds a lock on the log file
ParallelAlarm   | Dispatch the alert on multiple other Alarm instances

## Installation

The recommended way to install PingThis is through Composer :

```bash
composer require bpmconcept/ping-this
```

PingThis does not intend to provide a fully functional daemon out of the box. You are
still responsible for writing a configured daemon like in the previous example.
