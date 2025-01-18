<?php

declare(strict_types=1);

namespace App\Domain\Models\Timesheet\Template\ApprovedBy;

use App\Domain\Models\Common\UsesUuid;
use App\Domain\Models\Common\Uuid;

class ApprovedById implements Uuid
{
    use UsesUuid;
}
