<?php

namespace SourceBans\Command;

use SourceBans\Entity\ServerGroup;

class DeleteServerGroup
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
