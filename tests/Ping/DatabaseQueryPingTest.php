<?php

use PingThis\Ping\DatabaseQueryPing;

class DatabaseQueryPingTest extends \PHPUnit\Framework\TestCase
{
    public function testPingExpression()
    {
        $ping = new DatabaseQueryPing(0, 'sqlite::memory:', 'SELECT 42 as c1, "abc" as c2', 'response[0][0] == 42 and response[0][1] == "abc"');
        $this->assertTrue($ping->ping());
        
        $ping = new DatabaseQueryPing(0, 'sqlite::memory:', 'SELECT 42 as c1, "abc" as c2', 'response[0][0] == 43');
        $this->assertFalse($ping->ping());
    }
    
    public function testPingCallable()
    {
        $ping = new DatabaseQueryPing(0, 'sqlite::memory:', 'SELECT 42 as c1, "abc" as c2', function ($response) {
            $this->assertCount(1, $response);
            $this->assertEquals('42', $response[0]['c1']);
            $this->assertEquals('42', $response[0][0]);
            $this->assertEquals('abc', $response[0]['c2']);
            $this->assertEquals('abc', $response[0][1]);
            
            return true;
        });
        
        $this->assertTrue($ping->ping());
    }
}
