<?php

namespace SourceBans\CoreBundle\Steam\Command;

use SourceBans\CoreBundle\Entity\SteamAccountInterface;
use Steam\Command\User\GetPlayerSummaries;

/**
 * Fetch Steam profiles for SteamAccountInterface objects
 */
class GetSteamProfiles extends GetPlayerSummaries
{
    /**
     * @var SteamAccountInterface[]
     */
    protected $steamAccounts;

    /**
     * @param SteamAccountInterface[] $steamAccounts
     */
    public function __construct($steamAccounts)
    {
        if ($steamAccounts instanceof \Traversable) {
            $steamAccounts = iterator_to_array($steamAccounts, false);
        }

        $this->steamAccounts = array_values($this->filterInvalidSteamAccounts($steamAccounts));

        parent::__construct(array_map([$this, 'getSteamCommunityId'], $this->steamAccounts));
    }

    /**
     * @return SteamAccountInterface[]
     */
    public function getSteamAccounts()
    {
        return $this->steamAccounts;
    }

    /**
     * @param SteamAccountInterface[] $steamAccounts
     * @return SteamAccountInterface[]
     */
    private function filterInvalidSteamAccounts(array $steamAccounts)
    {
        return array_filter($steamAccounts, function (SteamAccountInterface $steamAccount) {
            return $steamAccount->getSteamAccountId() !== null;
        });
    }

    /**
     * @param SteamAccountInterface $steamAccount
     * @return string
     */
    private function getSteamCommunityId(SteamAccountInterface $steamAccount)
    {
        return gmp_strval(gmp_add('76561197960265728', $steamAccount->getSteamAccountId()));
    }
}
