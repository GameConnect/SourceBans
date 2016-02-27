<?php

namespace SourceBans\CoreBundle\Event;

use SourceBans\CoreBundle\Entity\Ban;
use Symfony\Component\EventDispatcher\Event;

/**
 * BanAdapterEvent
 */
class BanAdapterEvent extends Event
{
    /**
     * @var Ban
     */
    protected $ban;

    /**
     * @param Ban $ban
     */
    public function __construct(Ban $ban)
    {
        $this->ban = $ban;
    }

    /**
     * @return Ban
     */
    public function getBan()
    {
        return $this->ban;
    }
}
