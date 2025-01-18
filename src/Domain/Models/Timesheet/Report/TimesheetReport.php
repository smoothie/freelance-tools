<?php

declare(strict_types=1);

namespace App\Domain\Models\Timesheet\Report;

use App\Domain\Models\Common\EventRecordingCapabilities;
use App\Domain\Models\Common\UsesEventRecordingCapabilities;
use App\Domain\Models\Timesheet\Template\TimesheetTemplateId;
use App\Domain\Models\Timesheet\Template\TimesheetTemplateWasDrafted;

class TimesheetReport implements EventRecordingCapabilities
{
    use UsesEventRecordingCapabilities;

    private TimesheetReportId $timesheetReportId;

    public function __construct()
    {
    }

    public static function record(
        TimesheetReportId $timesheetReportId,
    ): self {
        $report = new self();
        $report->timesheetReportId = $timesheetReportId;

        //        $report->events[] = new TimesheetTemplateWasDrafted(
        //            timesheetTemplateId: $report->timesheetTemplateId,
        //        );

        return $report;
    }
}
