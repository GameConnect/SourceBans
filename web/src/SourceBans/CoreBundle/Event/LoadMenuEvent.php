<?php

namespace SourceBans\CoreBundle\Event;

use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * LoadMenuEvent
 */
class LoadMenuEvent extends Event
{
    /**
     * @var ItemInterface
     */
    private $menu;

    /**
     * @param ItemInterface $menu
     */
    public function __construct(ItemInterface $menu)
    {
        $this->menu = $menu;
    }

    /**
     * @param string|ItemInterface $child
     * @param array $options
     */
    public function addChild($child, array $options = [])
    {
        $this->menu->addChild($child, $options);
    }
}
