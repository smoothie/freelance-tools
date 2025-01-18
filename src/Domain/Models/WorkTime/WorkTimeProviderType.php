<?php

declare(strict_types=1);

namespace App\Domain\Models\WorkTime;

enum WorkTimeProviderType: string
{
    case CSV = 'CSV';
    case TOGGL = 'TOGGL';
}
