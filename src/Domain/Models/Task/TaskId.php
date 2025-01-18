<?php

declare(strict_types=1);

namespace App\Domain\Models\Task;

use App\Domain\Models\Common\UsesUuid;
use App\Domain\Models\Common\Uuid;

class TaskId implements Uuid
{
    use UsesUuid;
}
