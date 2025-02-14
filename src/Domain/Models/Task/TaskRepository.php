<?php

declare(strict_types=1);

namespace App\Domain\Models\Task;

interface TaskRepository
{
    public function getById(TaskId $taskId): Task;

    public function save(Task $task): void;
}
