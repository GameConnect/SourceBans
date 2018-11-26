<?php

namespace SourceBans\Command;

use SourceBans\Entity\Report;

class CreateReport
{
    /** @var Report */
    private $report;

    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    public function getReport(): Report
    {
        return $this->report;
    }
}
