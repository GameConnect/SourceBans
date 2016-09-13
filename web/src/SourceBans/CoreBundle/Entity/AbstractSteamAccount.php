<?php

namespace SourceBans\CoreBundle\Entity;

use JMS\Serializer\Annotation as Serialize;
use SourceBans\CoreBundle\Steam\SteamProfile;

/**
 * AbstractSteamAccount
 *
 * @Serialize\ExclusionPolicy("all")
 */
abstract class AbstractSteamAccount
{
    /**
     * @var SteamProfile
     *
     * @Serialize\Expose
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
