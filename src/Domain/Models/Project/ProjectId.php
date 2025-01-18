<?php

declare(strict_types=1);

namespace App\Domain\Models\Project;

use App\Domain\Models\Common\UsesUuid;
use App\Domain\Models\Common\Uuid;

class ProjectId implements Uuid
{
    use UsesUuid;
}
