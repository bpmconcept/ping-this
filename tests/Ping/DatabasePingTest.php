<?php

use PingThis\Ping\DatabasePing;

class DatabasePingTest extends \PHPUnit\Framework\TestCase
{
    public function testPing()
    {
        $ping = new DatabasePing(0, 'sqlite::memory:');
        $this->assertTrue($ping->ping());
    }
}
