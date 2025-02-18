<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Model\FilterCriteria;
use App\Domain\Model\Task;

interface WorkTimeProcessor
{
    /**
     * @return Task[]
     */
    public function getListOfTasks(FilterCriteria $filter): array;
}
