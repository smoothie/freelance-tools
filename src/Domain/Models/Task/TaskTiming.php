<?php

declare(strict_types=1);

namespace App\Domain\Models\Task;

use App\Domain\Models\Common\Duration;
use App\Domain\Models\Project\ProjectId;
use App\Domain\Models\Timing\TimingId;

class TaskTiming
{
    private TaskId $taskId;
    private ProjectId $projectId;
    private TimingId $timingId;
    private Duration $duration;

    private function __construct()
    {
    }

    public static function create(TaskId $taskId, ProjectId $projectId, TimingId $timingId, Duration $duration): self
    {
        $task = new self();

        $task->projectId = $projectId;
        $task->taskId = $taskId;
        $task->timingId = $timingId;
        $task->duration = $duration;

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

    public function timingId(): TimingId
    {
        return $this->timingId;
    }

    public function duration(): Duration
    {
        return $this->duration;
    }
}
