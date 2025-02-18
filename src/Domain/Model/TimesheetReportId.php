<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\Model\Common\UsesUuid;
use App\Domain\Model\Common\Uuid;

class TimesheetReportId implements Uuid
{
    use UsesUuid;
}
