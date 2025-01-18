<?php

declare(strict_types=1);

namespace App\Domain\Models\Timesheet\Template\ProvidedBy;

use App\Domain\Models\Common\UsesUuid;
use App\Domain\Models\Common\Uuid;

class ProvidedById implements Uuid
{
    use UsesUuid;
}
