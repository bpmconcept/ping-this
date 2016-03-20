<?php

namespace MarcBP\PingThis\Ping;

interface PingInterface
{
    public function getName();
    public function getLastError();
    public function getPingFrequency();
    public function ping();
}