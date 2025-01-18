<?php

declare(strict_types=1);

namespace App\Domain\Models\Timesheet\Template;

use App\Domain\Models\Common\UsesUuid;
use App\Domain\Models\Common\Uuid;

class TimesheetTemplateId implements Uuid
{
    use UsesUuid;
}
