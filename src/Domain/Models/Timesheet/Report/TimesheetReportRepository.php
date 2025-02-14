<?php

declare(strict_types=1);

namespace App\Domain\Models\Timesheet\Report;

interface TimesheetReportRepository
{
    public function getById(TimesheetReportId $timesheetReportId): TimesheetReport;

    public function save(TimesheetReport $workTimeProvider): void;
}
