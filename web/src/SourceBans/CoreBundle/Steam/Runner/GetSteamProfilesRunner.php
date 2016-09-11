<?php

namespace SourceBans\CoreBundle\Steam\Runner;

use SourceBans\CoreBundle\Steam\Command\GetSteamProfiles;
use Steam\Command\CommandInterface;

/**
 * Maps a GetPlayerSummaries response to SteamAccountInterface objects
 */
class GetSteamProfilesRunner extends GetPlayerSummariesRunner
{
    /**
     * @inheritdoc
     * @throws \InvalidArgumentException
     */
    public function run(CommandInterface $command, $result = null)
    {
        $steamProfiles = parent::run($command, $result);

        /** @var GetSteamProfiles $command */
        $steamAccounts = $command->getSteamAccounts();

        foreach ($steamProfiles as $i => $steamProfile) {
            $steamAccounts[$i]->setSteamProfile($steamProfile);
        }

        return $steamProfiles;
    }
}
