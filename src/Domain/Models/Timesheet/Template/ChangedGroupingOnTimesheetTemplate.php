<?php

declare(strict_types=1);

namespace App\Domain\Models\Timesheet\Template;

use App\Domain\Models\Grouping\GroupingCriteria;

class ChangedGroupingOnTimesheetTemplate
{
    public function __construct(
        private TimesheetTemplateId $timesheetTemplateId,
        private GroupingCriteria $new,
        private ?GroupingCriteria $old,
    ) {
    }

    public function timesheetTemplateId(): TimesheetTemplateId
    {
        return $this->timesheetTemplateId;
    }

    public function new(): GroupingCriteria
    {
        return $this->new;
    }

    public function old(): GroupingCriteria
    {
        return $this->old;
    }
}
