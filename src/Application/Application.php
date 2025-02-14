<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Models\Task\Task;
use App\Domain\Models\Task\TaskId;
use App\Domain\Models\Task\TaskRepository;
use App\Domain\Models\Timing\Timing;
use App\Domain\Models\Timing\TimingRepository;
use App\Domain\Models\WorkTime\WorkTimeProvider;
use App\Domain\Models\WorkTime\WorkTimeProviderRepository;

class Application implements ApplicationInterface
{
    public function __construct(
    ) {
    }

}
