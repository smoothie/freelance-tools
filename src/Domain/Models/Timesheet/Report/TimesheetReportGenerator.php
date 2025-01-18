<?php

declare(strict_types=1);

namespace App\Domain\Models\Timesheet\Report;

use App\Domain\Models\Timesheet\Template\TimesheetTemplate;

interface TimesheetReportGenerator
{
    public function generate(TimesheetTemplate $template): TimesheetReport;
}
