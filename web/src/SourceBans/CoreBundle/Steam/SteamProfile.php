<?php

namespace SourceBans\CoreBundle\Steam;

/**
 * Steam Community profile
 */
class SteamProfile
{
    const STATUS_AWAY             = 3;
    const STATUS_BUSY             = 2;
    const STATUS_LOOKING_TO_PLAY  = 6;
    const STATUS_LOOKING_TO_TRADE = 5;
    const STATUS_OFFLINE          = 0;
    const STATUS_ONLINE           = 1;
    const STATUS_SNOOZE           = 4;
    const VISIBILITY_FRIENDS      = 2;
    const VISIBILITY_PRIVATE      = 1;
    const VISIBILITY_PUBLIC       = 3;

    /**
     * @var array
     */
    private $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return boolean
     */
    public function canComment()
    {
        return isset($this->data['commentpermission']);
    }

    /**
     * @return string
     */
    public function getAvatar()
    {
        return $this->data['avatar'];
    }

    /**
     * @return string
     */
    public function getAvatarFull()
    {
        return $this->data['avatarfull'];
    }

    /**
     * @return string
     */
    public function getAvatarMedium()
    {
        return $this->data['avatarmedium'];
    }

    /**
     * @return integer
     */
    public function getCityId()
    {
        return isset($data['loccityid']) ? $data['loccityid'] : null;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return isset($data['loccountrycode']) ? $data['loccountrycode'] : null;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return isset($data['timecreated']) ? \DateTime::createFromFormat('U', $data['timecreated']) : null;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->data['personaname'];
    }

    /**
     * @return integer
     */
    public function getGameId()
    {
        return isset($data['gameid']) ? $data['gameid'] : null;
    }

    /**
     * @return string
     */
    public function getGameServerAddress()
    {
        return isset($data['gameserverip']) ? $data['gameserverip'] : null;
    }

    /**
     * @return string
     */
    public function getGameTitle()
    {
        return isset($data['gameextrainfo']) ? $data['gameextrainfo'] : null;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->data['steamid'];
    }

    /**
     * @return \DateTime
     */
    public function getLastOnline()
    {
        return \DateTime::createFromFormat('U', $this->data['lastlogoff']);
    }

    /**
     * @return string
     */
    public function getPrimaryGroupId()
    {
        return isset($data['primaryclanid']) ? $data['primaryclanid'] : null;
    }

    /**
     * @return string
     */
    public function getRealName()
    {
        return isset($data['realname']) ? $data['realname'] : null;
    }

    /**
     * @return string
     */
    public function getStateCode()
    {
        return isset($data['locstatecode']) ? $data['locstatecode'] : null;
    }

    /**
     * @return integer
     */
    public function getStatus()
    {
        return $this->data['personastate'];
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->data['profileurl'];
    }

    /**
     * @return integer
     */
    public function getVisibility()
    {
        return $this->data['communityvisibilitystate'];
    }

    /**
     * @return boolean
     */
    public function isConfigured()
    {
        return $this->data['profilestate'] == 1;
    }
}
