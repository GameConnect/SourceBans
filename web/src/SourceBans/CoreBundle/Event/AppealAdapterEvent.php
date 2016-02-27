<?php

namespace SourceBans\CoreBundle\Event;

use SourceBans\CoreBundle\Entity\Appeal;
use Symfony\Component\EventDispatcher\Event;

/**
 * AppealAdapterEvent
 */
class AppealAdapterEvent extends Event
{
    /**
     * @var Appeal
     */
    protected $appeal;

    /**
     * @param Appeal $appeal
     */
    public function __construct(Appeal $appeal)
    {
        $this->appeal = $appeal;
    }

    /**
     * @return Appeal
     */
    public function getAppeal()
    {
        return $this->appeal;
    }
}
