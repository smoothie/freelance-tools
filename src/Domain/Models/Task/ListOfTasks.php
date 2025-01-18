<?php

declare(strict_types=1);

namespace App\Domain\Models\Task;

class ListOfTasks
{
    /**
     * @param array<Task> $tasks
     */
    public function __construct(private array $tasks)
    {
    }

    public function tasks(): array
    {
        return $this->tasks;
    }
}
