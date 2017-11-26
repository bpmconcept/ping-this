<?php

namespace PingThis\StatusListener;

interface StatusListenerInterface
{
    /**
     * Called every second with the current status.
     *
     */
    public function update(array $statusList);
}