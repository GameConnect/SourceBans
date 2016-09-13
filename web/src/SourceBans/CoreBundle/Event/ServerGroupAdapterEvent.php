<?php

namespace SourceBans\CoreBundle\Event;

use SourceBans\CoreBundle\Entity\ServerGroup;
use Symfony\Component\EventDispatcher\Event;

/**
 * ServerGroupAdapterEvent
 */
class ServerGroupAdapterEvent extends Event
{
    /**
     * @var ServerGroup
     */
    protected $serverGroup;

    /**
     * @param ServerGroup $serverGroup
     */
    public function __construct(ServerGroup $serverGroup)
    {
        $this->serverGroup = $serverGroup;
    }

    /**
     * @return ServerGroup
     */
    public function getServerGroup()
    {
        return $this->serverGroup;
    }
}
