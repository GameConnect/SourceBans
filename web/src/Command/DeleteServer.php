<?php

namespace SourceBans\Command;

use SourceBans\Entity\Server;

class DeleteServer
{
    /** @var Server */
    private $server;

    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    public function getServer(): Server
    {
        return $this->server;
    }
}
