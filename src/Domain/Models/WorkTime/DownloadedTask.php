<?php

declare(strict_types=1);

namespace App\Domain\Models\WorkTime;

use App\Domain\Models\Task\TaskId;

class DownloadedTask
{
    public function __construct(private TaskId $taskId, private string $lastModifiedAt)
    {
    }

    public function taskId(): TaskId
    {
        return $this->taskId;
    }

    public function lastModifiedAt(): string
    {
        return $this->lastModifiedAt;
    }
}
