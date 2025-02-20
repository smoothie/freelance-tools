<?php

declare(strict_types=1);

namespace App\Domain\Model\Group;

use App\Domain\Model\Common\Duration;

class ListOfTasksInDays
{
    private Duration $duration;

    /**
     * @var TaskInAList[]
     */
    private array $tasks = [];

    public function __construct(private string $group)
    {
        $this->duration = Duration::fromSeconds(0);
    }

    public function group(): string
    {
        return $this->group;
    }

    public function duration(): Duration
    {
        return $this->duration;
    }

    /**
     * @return TaskInAList[]
     */
    public function tasks(): array
    {
        return $this->tasks;
    }

    public function addTaskToList(TaskInAList $newTask): void
    {
        $cloned = clone $this;

        $cloned->duration()->add($newTask->duration());

        foreach ($cloned->tasks as $task) {
            if ($newTask->group() === $task->group() && $newTask->description() === $task->description()) {
                $task->mergeDuration($newTask);
                $this->tasks = $cloned->tasks;
                $this->duration = $cloned->duration;

                return;
            }
        }

        $cloned->tasks[] = $newTask;
        $this->duration = $cloned->duration;
        $this->tasks = $cloned->tasks;
    }
}
