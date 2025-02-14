<?php

declare(strict_types=1);

namespace App\Domain\Models\Timesheet\Template;

use App\Domain\Models\Timesheet\Report\TimesheetReport;
use App\Domain\Models\Timesheet\Report\TimesheetReportId;

interface TimesheetTemplateRepository
{
    public function getById(TimesheetTemplateId $timesheetTemplateId): TimesheetTemplate;

    public function save(TimesheetTemplate $timesheetTemplate): void;
}
