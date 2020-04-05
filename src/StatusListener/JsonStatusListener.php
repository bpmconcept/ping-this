<?php

namespace PingThis\StatusListener;

use PingThis\PingStatus;
use PingThis\Group;

class JsonStatusListener implements StatusListenerInterface
{
    protected $file;

    public function __construct($file, $options = JSON_PRETTY_PRINT)
    {
        $this->file = $file;
        $this->options = $options;
    }

    public function update(Group $root)
    {
        $exportGroup = function (Group $group) use(&$exportGroup) {
            $data = array_map([$this, 'normalize'], $group->getPings());

            foreach ($group->getGroups() as $child) {
                $data[$child->getName()] = $exportGroup($child);
            }

            return $data;
        };

        $data = [
            'lastUpdate' => time(),
            'results' => $exportGroup($root),
        ];

        file_put_contents($this->file, json_encode($data, $this->options));
    }

    protected function normalize(PingStatus $status)
    {
        return [
            'ping' => $status->getPing()->getName(),
            'lastCheck' => $status->getLastCheck(),
            'status' => $status->getStatus(),
            'error' => $status->getPing()->getLastError(),
        ];
    }
}
