<?php

namespace SourceBans\CoreBundle\Entity;

use SourceBans\CoreBundle\Steam\SteamProfile;

/**
 * AbstractSteamAccount
 */
abstract class AbstractSteamAccount
{
    /**
     * @var SteamProfile
     */
    private $steamProfile;

    /**
     * @inheritdoc
     */
    public function getSteamProfile()
    {
        return $this->steamProfile;
    }

    /**
     * @inheritdoc
     */
    public function setSteamProfile(SteamProfile $steamProfile)
    {
        $this->steamProfile = $steamProfile;
    }
}
