<?php

declare(strict_types=1);

namespace App\Domain\Models\Timesheet\Template;

use App\Domain\Models\Filter\FilterCriteria;

class ChangedFilterOnTimesheetTemplate
{
    public function __construct(
        private TimesheetTemplateId $timesheetTemplateId,
        private FilterCriteria $new,
        private ?FilterCriteria $old,
    ) {
    }

    public function timesheetTemplateId(): TimesheetTemplateId
    {
        return $this->timesheetTemplateId;
    }

    public function new(): FilterCriteria
    {
        return $this->new;
    }

    public function old(): FilterCriteria
    {
        return $this->old;
    }
}
