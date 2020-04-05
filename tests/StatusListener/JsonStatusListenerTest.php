<?php

use PingThis\Daemon;
use PingThis\Group;
use PingThis\Ping\AbstractPing;
use PingThis\StatusListener\JsonStatusListener;

class TestPing extends AbstractPing
{
    public function __construct($name, $value)
    {
        parent::__construct(0);
        $this->name = $name;
        $this->value = $value;
    }

    public function getPingFrequency(): int
    {
        return 0;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLastError(): string
    {
        return 'error';
    }

    public function ping(): bool
    {
        return $this->value;
    }
}

class JsonStatusListenerTest extends \PHPUnit\Framework\TestCase
{
    public function testGroupsFlat()
    {
        $daemon = new Daemon();

        $daemon->registerGroup(new Group('group1', new TestPing('ping1', true)));
        $daemon->registerGroup(new Group('group2', new TestPing('ping2', false)));
        $daemon->registerGroup(new Group('group3', new TestPing('ping3', false)));

        $file = tempnam(sys_get_temp_dir(), 'json1');
        $daemon->registerStatusListener(new JsonStatusListener($file));
        $daemon->runOnce();

        $read = json_decode(file_get_contents($file), true);
        $this->assertEquals(['lastUpdate', 'results'], array_keys($read));
        $this->assertCount(3, $read['results']);
        $this->assertEquals(['group1', 'group2', 'group3'], array_keys($read['results']));
        $this->assertTrue($read['results']['group1'][0]['status']);
        $this->assertFalse($read['results']['group2'][0]['status']);
        $this->assertFalse($read['results']['group3'][0]['status']);
    }

    public function testGroupsRecursive()
    {
        $daemon = new Daemon();

        $group1 = new Group('group1', new TestPing('ping1', true));
        $group2 = new Group('group2', $group1, new TestPing('ping2', false));
        $group3 = new Group('group3', $group2, new TestPing('ping3', false));

        $daemon->registerGroup($group3);

        $file = tempnam(sys_get_temp_dir(), 'json2');
        $daemon->registerStatusListener(new JsonStatusListener($file));
        $daemon->runOnce();

        $read = json_decode(file_get_contents($file), true);
        $this->assertEquals(['lastUpdate', 'results'], array_keys($read));
        $this->assertCount(1, $read['results']);
        $this->assertContains('group3', array_keys($read['results']));
        $this->assertContains('group2', array_keys($read['results']['group3']));
        $this->assertContains('group1', array_keys($read['results']['group3']['group2']));
        $this->assertEquals('ping3', $read['results']['group3'][0]['ping']);
        $this->assertFalse($read['results']['group3'][0]['status']);
        $this->assertEquals('ping2', $read['results']['group3']['group2'][0]['ping']);
        $this->assertFalse($read['results']['group3']['group2'][0]['status']);
        $this->assertEquals('ping1', $read['results']['group3']['group2']['group1'][0]['ping']);
        $this->assertTrue($read['results']['group3']['group2']['group1'][0]['status']);
    }
}
