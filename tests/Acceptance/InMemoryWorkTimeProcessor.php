<?php

declare(strict_types=1);

namespace App\Tests\Acceptance;

use App\Domain\Model\FilterCriteria;
use App\Domain\Model\Task;
use App\Domain\Service\WorkTimeProcessor;

class InMemoryWorkTimeProcessor implements WorkTimeProcessor
{
    private array $tasks = [];

    public function getListOfTasks(FilterCriteria $filter): array
    {
        return $this->tasks;
    }

    public function addTask(Task $task): void
    {
        $this->tasks[] = $task;
    }
}
