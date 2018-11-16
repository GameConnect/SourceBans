<?php

namespace SourceBans\Command;

use SourceBans\Entity\Admin;
use SourceBans\Entity\Ban;

class UnbanBan
{
    /** @var Ban */
    private $ban;

    /** @var Admin */
    private $unbanAdmin;

    public function __construct(Ban $ban, Admin $unbanAdmin)
    {
        $this->ban = $ban;
        $this->unbanAdmin = $unbanAdmin;
    }

    public function getBan(): Ban
    {
        return $this->ban;
    }

    public function getUnbanAdmin(): Admin
    {
        return $this->unbanAdmin;
    }
}
