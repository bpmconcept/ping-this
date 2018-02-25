# PingThis

[![Build Status](https://travis-ci.org/bpmconcept/ping-this.svg?branch=master)](https://travis-ci.org/bpmconcept/ping-this)

PingThis is a lightweight PHP 5.5+ tool to build simple but functional headless monitoring systems.

## Example

``` php
use PingThis\Daemon;
use PingThis\SshSession;
use PingThis\Alarm\PhpEmailAlarm;
use PingThis\Ping\NetworkPing;
use PingThis\Ping\WebScraperPing;
use PingThis\Ping\SshCommandPing;
use PingThis\Ping\DatabasePing;
use PingThis\Ping\TlsCertificateExpirationPing;

$daemon = new Daemon();

// Check if the host correctly answers to ping every 10 seconds
$daemon->registerPing(new NetworkPing(10, 'domain.com'));

// Check if a webserver responds correctly to a HTTP request every 30 seconds
$daemon->registerPing(new WebScraperPing(30, 'GET', 'http://domain.com', 'response.getStatus() == 200'));
$daemon->registerPing(new WebScraperPing(30, 'GET', 'http://domain.com', 'crawler.filter(".css").count()'));

// Or equivalently using any PHP callable
$daemon->registerPing(new WebScraperPing(30, 'GET', 'http://domain.com', function ($response, $crawler) {
    return $response->getStatus() < 400 && $crawler->filter('.element')->text() === "Hello";
}));

// Check that a remote script or command correctly returns through SSH
$ssh = new SshSession('my.host.com');
$daemon->registerPing(new SshCommandPing(60, $ssh, '~/monitoring.sh', 'status == 0'));
$daemon->registerPing(new SshCommandPing(60, $ssh, 'cat /proc/loadavg | cut -d" " -f1', 'stdout < 4');

// Check every day that your certificate won't expire during the next week
$daemon->registerPing(new TlsCertificateExpirationPing(86400, 'ssl://domain.com:443', '+7 days'));

// Check if a remote SQL server is still up
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
or the `PingInterface`. The different built-in Pings rely on Symfony's
[Expression Language Component](http://symfony.com/doc/2.8/components/expression_language/syntax.html)
to allow a quick and easy construction of triggering logic but can be equivalently replaced
by a PHP callable.

### Built-in Pings

Name                            | Description
:------------------------------ | :---------------------------------------------------------------------------------------
NetworkPing                     | Sends a standard ICMP ping
HttpPing                        | Sends a HTTP request and checks only the returned code
WebScraperPing                  | Sends a HTTP request and get back a [Response](http://api.symfony.com/2.8/Symfony/Component/BrowserKit/Response.html), along with a [Crawler](http://symfony.com/doc/2.8/components/dom_crawler.html) instance
TlsCertificateExpirationPing    | Checks the expiration date of a web server's certificate
SshCommandPing                  | Executes a custom command through SSH and checks either stdout, stderr or exit code
StreamSocketCommandPing         | Sends a custom payload through a TCP/UDP/Unix socket and checks the response
DatabasePing                    | Establishes a connection to a database using PDO
DatabaseQueryPing               | Executes a SQL query on a database using PDO
LdapSearchPing                  | Executes a query on a LDAP server and checks the response

### Built-in Alarms

Name            | Description
:-------------- | :---------------------------------------------------------------------------------------
PhpEmailAlarm   | Send an email, using the PHP's mail() function
StreamAlarm     | Simply logs the alarms events to a given open stream (stdout or file for instance)
ParallelAlarm   | Dispatch the alert on multiple other Alarm instances

## Installation

The recommended way to install PingThis is through Composer :

```
composer require marcbp/ping-this
```

PingThis does not intend to provide a fully functional daemon out of the box. You are
still responsible for writing a configured daemon like in the previous example. Thereafter,
a real daemon can be registered to your favorite init system like [systemd](https://freedesktop.org/wiki/Software/systemd/),
[upstart](https://help.ubuntu.com/community/UbuntuBootupHowto) or [supervisor](http://supervisord.org/).

