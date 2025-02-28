<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Acceptance;

use Smoothie\FreelanceTools\Domain\Model\FilterCriteria;
use Smoothie\FreelanceTools\Domain\Model\Task;
use Smoothie\FreelanceTools\Domain\Service\WorkTimeProcessor;

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
