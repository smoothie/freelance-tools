<?php

declare(strict_types=1);

namespace App\Domain\Models\Task;

use App\Domain\Models\Common\Duration;
use App\Domain\Models\Common\EventRecordingCapabilities;
use App\Domain\Models\Common\UsesEventRecordingCapabilities;
use App\Domain\Models\Project\ProjectId;
use App\Domain\Models\Timing\TimingId;

class Task implements EventRecordingCapabilities
{
    use UsesEventRecordingCapabilities;

    private TaskId $taskId;
    private ProjectId $projectId;
    private string $description;
    private Duration $duration;
    private string $importedAt;
    private string $lastModifiedAt;
    private array $tags;

    /**
     * @var TaskTiming[]
     */
    private array $timings;

    private function __construct()
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

    public static function import(
        TaskId $taskId,
        ProjectId $projectId,
        string $description,
        string $importedAt,
        string $lastModifiedAt,
    ): self {
        $task = new self();
        $task->taskId = $taskId;
        $task->projectId = $projectId;
        $task->description = $description;
        $task->importedAt = $importedAt;
        $task->lastModifiedAt = $lastModifiedAt;
        $task->duration = Duration::fromSeconds(0);

        $task->events[] = new TaskWasImported(
            taskId: $taskId,
            projectId: $projectId,
            description: $description,
            importedAt: $importedAt,
            lastModifiedAt: $lastModifiedAt,
        );

        return $task;
    }

    public function trackTime(TimingId $timingId, Duration $duration): void
    {
        $this->timings[] = TaskTiming::create($this->taskId, $this->projectId, $timingId, $duration);
        $this->duration->add($duration);

        $this->events[] = new TrackedTimeOnTask(
            taskId: $this->taskId,
            projectId: $this->projectId,
            timingId: $timingId,
            newDuration: $this->duration,
            increasedDurationBy: $duration,
        );
    }

    public function untrackTime(TimingId $timingId, Duration $duration): void
    {
        foreach ($this->timings as $key => $timing) {
            if (! $timing->timingId()->equals($timingId)) {
                continue;
            }

            unset($this->timings[$key]);
            $this->events[] = new UntrackedTimeOnTask(
                taskId: $this->taskId,
                projectId: $this->projectId,
                timingId: $timingId,
                newDuration: $this->duration,
                decreasedDurationBy: $duration,
            );
        }

        $this->timings = array_values($this->timings);
        $this->duration->subtract($duration);
    }
}
