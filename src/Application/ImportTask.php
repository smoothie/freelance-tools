<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Models\Common\DateTime;
use App\Domain\Models\Project\ProjectId;
use App\Domain\Models\Task\TaskId;

class ImportTask
{
    public function __construct(
        private string $taskId,
        private string $projectId,
        private string $description,
        private string $importedAt,
    ) {
    }

    /**
     * @return TaskId
     */
    public function taskId(): TaskId
    {
        return TaskId::fromString($this->taskId);
    }

    /**
     * @return ProjectId
     */
    public function projectId(): ProjectId
    {
        return ProjectId::fromString($this->projectId);
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return $this->description;
    }

    public function importedAt(): DateTime
    {
        return DateTime::fromString($this->importedAt);
    }
}
