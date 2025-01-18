<?php

declare(strict_types=1);

namespace App\Domain\Models\Timesheet\Report;

use App\Domain\Models\Common\UsesUuid;
use App\Domain\Models\Common\Uuid;

class TimesheetReportId implements Uuid
{
    use UsesUuid;
}
