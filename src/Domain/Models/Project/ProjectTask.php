<?php

declare(strict_types=1);

namespace App\Domain\Models\Project;

use App\Domain\Models\Task\TaskId;

class ProjectTask
{
    private ProjectId $projectId;
    private TaskId $taskId;

    private function __construct()
    {
    }

    public static function create(ProjectId $projectId, TaskId $taskId): self
    {
        $task = new self();

        $task->projectId = $projectId;
        $task->taskId = $taskId;

        return $task;
    }

    public function projectId(): ProjectId
    {
        return $this->projectId;
    }

    public function taskId(): TaskId
    {
        return $this->taskId;
    }
}
