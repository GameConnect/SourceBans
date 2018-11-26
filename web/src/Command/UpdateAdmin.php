<?php

namespace SourceBans\Command;

use SourceBans\Entity\Admin;

class UpdateAdmin
{
    /** @var Admin */
    private $admin;

    public function __construct(Admin $admin)
    {
        $this->admin = $admin;
    }

    public function getAdmin(): Admin
    {
        return $this->admin;
    }
}