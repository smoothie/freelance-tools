<?php

declare(strict_types=1);

namespace App\Domain\Models\Timesheet\Template;

class TimesheetTemplateWasDrafted
{
    public function __construct(
        private TimesheetTemplateId $timesheetTemplateId,
    ) {
    }

    public function timesheetTemplateId(): TimesheetTemplateId
    {
        return $this->timesheetTemplateId;
    }
}
