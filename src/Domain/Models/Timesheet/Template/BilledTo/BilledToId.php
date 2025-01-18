<?php

declare(strict_types=1);

namespace App\Domain\Models\Timesheet\Template\BilledTo;

use App\Domain\Models\Common\UsesUuid;
use App\Domain\Models\Common\Uuid;

class BilledToId implements Uuid
{
    use UsesUuid;
}
