<?php

declare(strict_types=1);

namespace App\Domain\Models\Timesheet\Template\BilledBy;

use App\Domain\Models\Common\UsesUuid;
use App\Domain\Models\Common\Uuid;

class BilledById implements Uuid
{
    use UsesUuid;
}
