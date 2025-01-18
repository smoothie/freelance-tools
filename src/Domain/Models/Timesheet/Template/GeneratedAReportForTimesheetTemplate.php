<?php

declare(strict_types=1);

namespace App\Domain\Models\Timesheet\Template;

use App\Domain\Models\Timesheet\Report\TimesheetReport;

class GeneratedAReportForTimesheetTemplate
{
    public function __construct(
        private TimesheetTemplateId $templateId,
        private TimesheetReport $report,
    ) {
    }

    public function templateId(): TimesheetTemplateId
    {
        return $this->templateId;
    }

    public function report(): TimesheetReport
    {
        return $this->report;
    }
}
