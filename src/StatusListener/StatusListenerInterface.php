<?php

namespace PingThis\StatusListener;

use PingThis\Group;

interface StatusListenerInterface
{
    /**
     * Called every second, when all the pings from the root group have been
     * tested.
     */
    public function update(Group $root);
}
