<?php

namespace PingThis\StatusListener;

use PingThis\PingStatus;

class JsonStatusListener implements StatusListenerInterface
{
    protected $file;
    
    public function __construct($file)
    {
        $this->file = $file;
    }
    
    public function update(array $statusList)
    {
        $data = array_map([$this, 'normalize'], $statusList);
        file_put_contents($this->file, \json_encode($data));
    }
    
    protected function normalize(PingStatus $status)
    {
        return [
            'ping' => $status->getPing()->getName(),
            'status' => $status->getStatus(),
            'lastCheck' => $status->getLastCheck(),
        ];
    }
}