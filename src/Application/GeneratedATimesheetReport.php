<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Application;

use Smoothie\FreelanceTools\Domain\Model\PerformancePeriod;
use Smoothie\FreelanceTools\Domain\Model\TimesheetReport;
use Smoothie\FreelanceTools\Domain\Model\TimesheetReportId;

class GeneratedATimesheetReport
{
    public function __construct(
        private TimesheetReport $timesheetReport,
        private string $exportFormat,
        private string $rendered,
        private PerformancePeriod $performancePeriod,
    ) {
    }

    public function timesheetReportId(): TimesheetReportId
    {
        return $this->timesheetReport->timesheetReportId();
    }

    public function timesheetReport(): TimesheetReport
    {
        return $this->timesheetReport;
    }

    public function exportFormat(): string
    {
        return $this->exportFormat;
    }

    public function rendered(): string
    {
        return $this->rendered;
    }

    public function performancePeriod(): PerformancePeriod
    {
        return $this->performancePeriod;
    }
}
