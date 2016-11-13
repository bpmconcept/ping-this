<?php

use PingThis\Ping\DatabasePing;

class DatabasePingTest extends PHPUnit_Framework_TestCase
{
    public function testPing()
    {
        $ping = new DatabasePing(0, 'sqlite::memory:');
        $this->assertTrue($ping->ping());
    }
}
