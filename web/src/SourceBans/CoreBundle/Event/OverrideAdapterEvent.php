<?php

namespace SourceBans\CoreBundle\Event;

use SourceBans\CoreBundle\Entity\Override;
use Symfony\Component\EventDispatcher\Event;

/**
 * OverrideAdapterEvent
 */
class OverrideAdapterEvent extends Event
{
    /**
     * @var Override
     */
    protected $override;

    /**
     * @param Override $override
     */
    public function __construct(Override $override)
    {
        $this->override = $override;
    }

    /**
     * @return Override
     */
    public function getOverride()
    {
        return $this->override;
    }
}
