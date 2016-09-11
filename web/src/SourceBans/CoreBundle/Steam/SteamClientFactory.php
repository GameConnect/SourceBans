<?php

namespace SourceBans\CoreBundle\Steam;

use GuzzleHttp\Client;
use Steam\Configuration;
use Steam\Runner\DecodeJsonStringRunner;
use Steam\Runner\GuzzleRunner;
use Steam\Steam;
use Steam\Utility\GuzzleUrlBuilder;

/**
 * Factory for the Steam client
 */
class SteamClientFactory
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param string $apiKey
     */
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @return Steam
     */
    public function createClient()
    {
        $steam = new Steam(new Configuration([
            Configuration::STEAM_KEY => $this->apiKey,
        ]));
        $steam->addRunner(new GuzzleRunner(new Client(), new GuzzleUrlBuilder()));
        $steam->addRunner(new DecodeJsonStringRunner());

        return $steam;
    }
}
