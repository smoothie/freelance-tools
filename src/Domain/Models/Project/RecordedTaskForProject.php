<?php

declare(strict_types=1);

namespace App\Domain\Models\Project;

use App\Domain\Models\Task\TaskId;

class RecordedTaskForProject
{
    public function __construct(
        private ProjectId $projectId,
        private TaskId $taskId,
    ) {
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
