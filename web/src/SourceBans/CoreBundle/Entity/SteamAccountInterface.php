<?php

namespace SourceBans\CoreBundle\Entity;

use SourceBans\CoreBundle\Steam\SteamProfile;

/**
 * SteamAccountInterface
 */
interface SteamAccountInterface
{
    /**
     * Returns the Steam Account ID
     *
     * @return integer
     */
    public function getSteamAccountId();

    /**
     * @return SteamProfile
     */
    public function getSteamProfile();

    /**
     * @param SteamProfile $steamProfile
     */
    public function setSteamProfile(SteamProfile $steamProfile);
}
