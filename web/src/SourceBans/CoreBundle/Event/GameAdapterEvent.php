<?php

namespace SourceBans\CoreBundle\Event;

use SourceBans\CoreBundle\Entity\Game;
use Symfony\Component\EventDispatcher\Event;

/**
 * GameAdapterEvent
 */
class GameAdapterEvent extends Event
{
    /**
     * @var Game
     */
    protected $game;

    /**
     * @param Game $game
     */
    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    /**
     * @return Game
     */
    public function getGame()
    {
        return $this->game;
    }
}
