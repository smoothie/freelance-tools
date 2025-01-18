<?php

declare(strict_types=1);

namespace App\Domain\Models\Timesheet\Template;

use App\Domain\Models\Pattern\TitlePattern;

class ChangedTitlePatternOnTimesheetTemplate
{
    public function __construct(
        private TimesheetTemplateId $timesheetTemplateId,
        private TitlePattern $new,
        private ?TitlePattern $old,
    ) {
    }

    public function timesheetTemplateId(): TimesheetTemplateId
    {
        return $this->timesheetTemplateId;
    }

    public function new(): TitlePattern
    {
        return $this->new;
    }

    public function old(): TitlePattern
    {
        return $this->old;
    }
}
