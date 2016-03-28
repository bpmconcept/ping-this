<?php

namespace MarcBP\PingThis\Ping;

interface PingInterface
{
    /**
     * Returns a string describing the last error, ie. the reason of the last
     * false response of ping().
     */
    public function getLastError();

    /**
     * Returns a wanted frequency for this ping. This frequency may not be
     * regular, particularly if some other pings are slow.
     */
    public function getPingFrequency();

    /**
     * Indicates if this ping succeeded or failed by returning respectively true
     * or false. The ping is responsible for saving internal data in case of
     * error, in order to be able to give a descriptive message later for the
     * alarm.
     */
    public function ping();
}
