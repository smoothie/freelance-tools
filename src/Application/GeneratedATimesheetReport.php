<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Model\TimesheetReport;
use App\Domain\Model\TimesheetReportId;

class GeneratedATimesheetReport
{
    public function __construct(
        private TimesheetReport $timesheetReport,
        private string $exportFormat,
        private string $rendered,
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

    public function rendered(): string
    {
        return $this->rendered;
    }

    public function exportFormat(): string
    {
        return $this->exportFormat;
    }
}
