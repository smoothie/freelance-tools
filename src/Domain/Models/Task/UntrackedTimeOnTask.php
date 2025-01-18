<?php

declare(strict_types=1);

namespace App\Domain\Models\Task;

use App\Domain\Models\Common\Duration;
use App\Domain\Models\Project\ProjectId;
use App\Domain\Models\Timing\TimingId;

class UntrackedTimeOnTask
{
    public function __construct(
        private TaskId $taskId,
        private ProjectId $projectId,
        private TimingId $timingId,
        private Duration $newDuration,
        private Duration $decreasedDurationBy,
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

    public function timingId(): TimingId
    {
        return $this->timingId;
    }

    public function newDuration(): Duration
    {
        return $this->newDuration;
    }

    public function decreasedDurationBy(): Duration
    {
        return $this->decreasedDurationBy;
    }
}
