<?php

declare(strict_types=1);

namespace App\Domain\Models\Task;

use App\Domain\Models\Common\DateTime;
use App\Domain\Models\Project\ProjectId;

class TaskWasImported
{
    public function __construct(
        private TaskId $taskId,
        private ProjectId $projectId,
        private string $description,
        private DateTime $importedAt,
        private DateTime $lastModifiedAt,
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

    public function importedAt(): DateTime
    {
        return $this->importedAt;
    }

    public function lastModifiedAt(): DateTime
    {
        return $this->lastModifiedAt;
    }
}
