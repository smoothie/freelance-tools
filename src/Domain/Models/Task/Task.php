<?php

declare(strict_types=1);

namespace App\Domain\Models\Task;

use App\Domain\Models\Common\DateTime;
use App\Domain\Models\Common\Duration;
use App\Domain\Models\Common\EventRecordingCapabilities;
use App\Domain\Models\Common\UsesEventRecordingCapabilities;
use App\Domain\Models\Project\ProjectId;
use App\Domain\Models\Timing\Timing;

class Task implements EventRecordingCapabilities
{
    use UsesEventRecordingCapabilities;

    private TaskId $taskId;
    private ProjectId $projectId;
    private string $description;
    private Duration $duration;
    private DateTime $importedAt;
    private DateTime $lastModifiedAt;
    private array $tags;

    /**
     * @var TaskTiming[]
     */
    private array $timings;

    private function __construct()
    {
    }

    public static function import(
        TaskId $taskId,
        ProjectId $projectId,
        string $description,
        DateTime $importedAt,
        DateTime $lastModifiedAt,
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

    public function taskId(): TaskId
    {
        return $this->taskId;
    }

    public function lastModifiedAt(): DateTime
    {
        return $this->lastModifiedAt;
    }

    public function trackTime(Timing $timing): void
    {
        $timingId = $timing->getTimingId();
        $duration = $timing->getDuration();

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

    public function untrackTime(Timing $untracked): void
    {
        foreach ($this->timings as $key => $timing) {
            if (! $timing->timingId()->equals($untracked->getTimingId())) {
                continue;
            }

            unset($this->timings[$key]);
            $this->events[] = new UntrackedTimeOnTask(
                taskId: $this->taskId,
                projectId: $this->projectId,
                timingId: $untracked->getTimingId(),
                newDuration: $this->duration,
                decreasedDurationBy: $untracked->getDuration(),
            );
        }

        $this->timings = array_values($this->timings);
        $this->duration->subtract($untracked->getDuration());
    }
}
