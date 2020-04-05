<?php

namespace PingThis;

use PingThis\Ping\PingInterface;

class Group
{
    protected $name;
    protected $pings = [];
    protected $groups = [];

    public function __construct(string $name, ...$children)
    {
        $this->name = $name;

        foreach ($children as $child) {
            if ($child instanceof PingInterface) {
                $this->registerPing($child);
            } elseif ($child instanceof Group) {
                $this->registerGroup($child);
            } else {
                trigger_error('Group constructor is expecting Group or PingInterface children', E_USER_ERROR);
            }
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function registerPing(PingInterface $ping)
    {
        $this->pings[] = new PingStatus($ping);
    }

    public function getPings()
    {
        return $this->pings;
    }

    public function registerGroup(Group $group)
    {
        $this->groups[] = $group;
    }

    public function getGroups()
    {
        return $this->groups;
    }
}
