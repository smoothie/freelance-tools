<?php

declare(strict_types=1);

namespace App\Domain\Models\Timesheet\Template\PerformancePeriod;

use App\Domain\Models\Common\UsesUuid;
use App\Domain\Models\Common\Uuid;

class PerformancePeriodId implements Uuid
{
    use UsesUuid;
}
