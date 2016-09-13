<?php

namespace SourceBans\CoreBundle\Event;

use SourceBans\CoreBundle\Entity\Report;
use Symfony\Component\EventDispatcher\Event;

/**
 * ReportAdapterEvent
 */
class ReportAdapterEvent extends Event
{
    /**
     * @var Report
     */
    protected $report;

    /**
     * @param Report $report
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    /**
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }
}
