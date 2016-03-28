# PingThis

[![Build Status](https://travis-ci.org/marcbp/ping-this.svg?branch=master)](https://travis-ci.org/marcbp/ping-this)

PingThis is an extremely lightweight PHP 5.4+ tool to build a simple but functional headless monitoring system.

## Installation

The recommended way to install PingThis is through Composer :

```
composer require marcbp/ping-this
```

## Example

``` php
use MarcBP\PingThis\Daemon;
use MarcBP\PingThis\Alarm\PhpEmailAlarm;
use MarcBP\PingThis\Ping\NetworkPing;

$daemon = new Daemon();

$daemon->registerAlarm(new PhpEmailAlarm('your@email.com'));
$daemon->registerPing(new NetworkPing('host1', 20, 'host1.domain.com'));
$daemon->registerPing(new NetworkPing('host2', 20, 'host2.domain.com'));

$daemon->run();
```

## Quick description

PingThis aims to provide a simple and effective way for monitoring whatever your want.
Configure a daemon with one Alarm and one or multiple Pings. The Daemon periodically
verifies each Ping and, in case of failing, triggers the Alarm. Any class could act
like an Alarm or a Ping, provided that it implements respectively the `AlarmInterface`
or the `PingInterface`.

### Built-in pings

Name            | Description
:-------------- | :---------------------------------------------------------------------------------------
NetworkPing     | A standard ICMP ping, or, failing that, an attempt to open a socket on a specified port
HttpHeaderPing  | Check through headers only if a web server answers correctly to a GET request

### Built-in alarms

Name            | Description
:-------------- | :---------------------------------------------------------------------------------------
PhpEmailAlarm   | Send an email, using the PHP's mail() function
StreamAlarm     | Simply logs the alarms events to a given open stream (stdout or file for instance)
ParallelAlarm   | Dispatch the alert on multiple other Alarm instances