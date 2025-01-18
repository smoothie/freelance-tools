<?php

declare(strict_types=1);

namespace App\Domain\Models\Contractor;

use App\Domain\Models\Common\UsesUuid;
use App\Domain\Models\Common\Uuid;

class CustomerId implements Uuid
{
    use UsesUuid;
}
