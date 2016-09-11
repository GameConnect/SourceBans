<?php

namespace SourceBans\CoreBundle\Steam;

use SourceBans\CoreBundle\Entity\SteamAccountInterface;
use SourceBans\CoreBundle\Steam\Command\GetSteamProfiles;
use SourceBans\CoreBundle\Steam\Exception\MissingSteamApiKeyException;
use SourceBans\CoreBundle\Steam\Runner\GetSteamProfilesRunner;
use Steam\Steam;

/**
 * Steam Community Data and Web API
 */
class SteamApi
{
    /**
     * @var SteamClientFactory
     */
    private $clientFactory;

    /**
     * @param SteamClientFactory $clientFactory
     */
    public function __construct(SteamClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    /**
     * @return Steam
     * @throws MissingSteamApiKeyException
     */
    public function createClient()
    {
        $client = $this->clientFactory->createClient();

        if ($client->getConfig()->getSteamKey() == '') {
            throw new MissingSteamApiKeyException;
        }

        return $client;
    }

    /**
     * @param SteamAccountInterface[] $steamAccounts
     * @throws MissingSteamApiKeyException
     */
    public function fetchSteamProfiles($steamAccounts)
    {
        $client = $this->createClient();
        $client->addRunner(new GetSteamProfilesRunner());
        $client->run(new GetSteamProfiles($steamAccounts));
    }
}
