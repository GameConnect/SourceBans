<?php

namespace SourceBans\Command;

use SourceBans\Entity\ServerGroup;

class UpdateServerGroup
{
    /** @var ServerGroup */
    private $serverGroup;

    public function __construct(ServerGroup $serverGroup)
    {
        $this->serverGroup = $serverGroup;
    }

    public function getServerGroup(): ServerGroup
    {
        return $this->serverGroup;
    }
}
