# PingThis

[![Build Status](https://travis-ci.org/marcbp/ping-this.svg?branch=master)](https://travis-ci.org/marcbp/ping-this)

PingThis is an extremely lightweight PHP 5.4+ tool to build a simple but functional headless monitoring system.

## Installation

The recommended way to install PingThis is through Composer :

```
composer require marcbp/ping-this
```

### Example

``` php
use MarcBP\PingThis\Daemon;
use MarcBP\PingThis\Alarm\PhpEmailAlarm;
use MarcBP\PingThis\Ping\NetworkPing;

$daemon = new Daemon();

$daemon->registerAlarm(new PhpEmailAlarm('your@email.com'));
$daemon->registerPing(new NetworkPing('ping', 20, 'your.host.com'));

$daemon->run();
```

### Built-in pings

Name            | Description
:-------------- | :---------------------------------------------------------------------------------------
NetworkPing     | A standard ICMP ping, or, failing that, an attempt to open a socket on a specified port

### Built-in alarms

Name            | Description
:-------------- | :---------------------------------------------------------------------------------------
PhpEmailAlarm   | Send an email, using the PHP's mail() function
StreamAlarm     | Simply logs the alarms events to a given open stream (stdout or file for instance)
