<?php

namespace SourceBans\CoreBundle\Event;

use SourceBans\CoreBundle\Entity\Group;
use Symfony\Component\EventDispatcher\Event;

/**
 * GroupAdapterEvent
 */
class GroupAdapterEvent extends Event
{
    /**
     * @var Group
     */
    protected $group;

    /**
     * @param Group $group
     */
    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }
}
