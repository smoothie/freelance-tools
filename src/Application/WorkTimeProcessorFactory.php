<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Models\Filter\FilterCriteria;
use App\Domain\Models\Task\ListOfTasks;
use App\Domain\Models\WorkTime\Credentials\Credentials;
use App\Domain\Models\WorkTime\WorkTimeProcessor;
use App\Domain\Models\WorkTime\WorkTimeProviderType;

interface WorkTimeProcessorFactory
{
    public function create(WorkTimeProviderType $workTimeProviderType): WorkTimeProcessor;
}
