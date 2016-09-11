<?php

namespace SourceBans\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * LoadRolesEvent
 */
class LoadRolesEvent extends Event
{
    /**
     * @var array
     */
    protected $roles = [];

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param string $role
     */
    public function addRole($role)
    {
        $this->roles[] = $role;
    }
}
