<?php

declare(strict_types=1);

namespace App\Domain\Models\WorkTime;

use App\Domain\Models\Filter\FilterCriteria;
use App\Domain\Models\Task\ListOfTasks;
use App\Domain\Models\WorkTime\Credentials\Credentials;

interface WorkTimeProcessor
{
    public function getListOfTasks(FilterCriteria $filter, Credentials $credentials): ListOfTasks;
}
