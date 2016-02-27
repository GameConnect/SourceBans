<?php

namespace SourceBans\CoreBundle\Event;

use SourceBans\CoreBundle\Entity\Admin;
use Symfony\Component\EventDispatcher\Event;

/**
 * AdminAdapterEvent
 */
class AdminAdapterEvent extends Event
{
    /**
     * @var Admin
     */
    protected $admin;

    /**
     * @param Admin $admin
     */
    public function __construct(Admin $admin)
    {
        $this->admin = $admin;
    }

    /**
     * @return Admin
     */
    public function getAdmin()
    {
        return $this->admin;
    }
}
