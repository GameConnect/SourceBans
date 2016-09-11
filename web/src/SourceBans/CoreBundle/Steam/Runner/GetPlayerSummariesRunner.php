<?php

namespace SourceBans\CoreBundle\Steam\Runner;

use SourceBans\CoreBundle\Steam\SteamProfile;
use Steam\Command\CommandInterface;
use Steam\Runner\AbstractRunner;
use Steam\Runner\RunnerInterface;

/**
 * Transforms a GetPlayerSummaries response to SteamProfile objects
 */
class GetPlayerSummariesRunner extends AbstractRunner implements RunnerInterface
{
    /**
     * @inheritdoc
     * @throws \InvalidArgumentException
     */
    public function run(CommandInterface $command, $result = null)
    {
        if (!isset($result['response']['players'])) {
            throw new \InvalidArgumentException('The response does not contain any players.');
        }

        return array_map(
            function (array $data) {
                return new SteamProfile($data);
            },
            $result['response']['players']
        );
    }
}
