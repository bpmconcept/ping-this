# PingThis

[![Build Status](https://travis-ci.org/marcbp/ping-this.svg?branch=master)](https://travis-ci.org/marcbp/ping-this)

PingThis is an extremely lightweight PHP 5.4+ tool to build a simple but functional headless monitoring system.

## Example

``` php
use PingThis\Daemon;
use PingThis\SshSession;
use PingThis\Alarm\PhpEmailAlarm;
use PingThis\Ping\NetworkPing;
use PingThis\Ping\HttpHeaderPing;
use PingThis\Ping\SshCommandPing;
use PingThis\Ping\TlsCertificateExpirationPing;
use PingThis\Matcher\LessThan;

$daemon = new Daemon();

// Check if the host correctly answers to ping every 10 seconds
$daemon->registerPing(new NetworkPing(10, 'domain.com'));

// Check if a web server correctly answers to HTTP requests every minute
$daemon->registerPing(new HttpHeaderPing(60, 'http://domain.com'));

// Check every day that your certificate won't expire during the next week
$daemon->registerPing(new TlsCertificateExpirationPing(86400, 'ssl://domain.com:443', '+7 days'));

// Check that a remote script or command correctly returns through SSH
$ssh = new SshSession('my.host.com');
$daemon->registerPing(new SshCommandPing(60, $ssh, '~/monitoring.sh'));
$daemon->registerPing(new SshCommandPing(60, $ssh, 'cat /proc/loadavg | cut -d' ' -f1', new LessThan(4));

// Otherwise send an email to alert an admin
$daemon->registerAlarm(new PhpEmailAlarm('your@email.com'));

$daemon->run();
```

## Quick description

PingThis aims to provide a simple and effective way for monitoring whatever your want.
Configure a daemon with one Alarm and one or multiple Pings. The Daemon periodically
verifies each Ping and, in case of failing, triggers the Alarm. Any class could act
like an Alarm or a Ping, provided that it implements respectively the `AlarmInterface`
or the `PingInterface`.

### Built-in Pings

Name                            | Description
:------------------------------ | :---------------------------------------------------------------------------------------
NetworkPing                     | A standard ICMP ping, or, failing that, an attempt to open a socket on a specified port
HttpHeaderPing                  | Check through headers only if a web server answers correctly to a GET request
TlsCertificateExpirationPing    | Check the expiration date of a web server's certificate
SshCommandPing                  | Run a custom command through SSH and check either exit code or response content

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

