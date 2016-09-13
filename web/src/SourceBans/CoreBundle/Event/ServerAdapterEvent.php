<?php

namespace SourceBans\CoreBundle\Event;

use SourceBans\CoreBundle\Entity\Server;
use Symfony\Component\EventDispatcher\Event;

/**
 * ServerAdapterEvent
 */
class ServerAdapterEvent extends Event
{
    /**
     * @var Server
     */
    protected $server;

    /**
     * @param Server $server
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    /**
     * @return Server
     */
    public function getServer()
    {
        return $this->server;
    }
}
