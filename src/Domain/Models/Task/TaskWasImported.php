<?php

declare(strict_types=1);

namespace App\Domain\Models\Task;

use App\Domain\Models\Project\ProjectId;

class TaskWasImported
{
    public function __construct(
        private TaskId $taskId,
        private ProjectId $projectId,
        private string $description,
        private string $importedAt,
        private string $lastModifiedAt,
    ) {
    }

    public function taskId(): TaskId
    {
        return $this->taskId;
    }

    public function projectId(): ProjectId
    {
        return $this->projectId;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function importedAt(): string
    {
        return $this->importedAt;
    }

    public function lastModifiedAt(): string
    {
        return $this->lastModifiedAt;
    }
}
