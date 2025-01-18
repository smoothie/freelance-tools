<?php

declare(strict_types=1);

namespace App\Domain\Models\WorkTime;

use App\Domain\Models\Common\UsesUuid;
use App\Domain\Models\Common\Uuid;

class WorkTimeProviderId implements Uuid
{
    use UsesUuid;
}
