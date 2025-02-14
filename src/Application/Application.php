<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Models\Task\Task;
use App\Domain\Models\Task\TaskId;
use App\Domain\Models\Task\TaskRepository;
use App\Domain\Models\Timing\Timing;
use App\Domain\Models\Timing\TimingRepository;
use App\Domain\Models\WorkTime\WorkTimeProvider;
use App\Domain\Models\WorkTime\WorkTimeProviderRepository;

class Application implements ApplicationInterface
{
    public function __construct(
        private EventDispatcher $eventDispatcher,
        private TaskRepository $taskRepository,
        private TimingRepository $timingRepository,
        private WorkTimeProviderRepository $workTimeProviderRepository,
        private WorkTimeProcessorFactory $workTimeProcessorFactory,
        private Clock $clock,
    ) {
    }

    public function importATask(ImportTask $command): void
    {
        $task = Task::import(
            $command->taskId(),
            $command->projectId(),
            $command->description(),
            $command->importedAt(),
            $this->clock->currentTime(),
        );

        $this->taskRepository->save($task);

        $this->eventDispatcher->dispatchAll($task->releaseEvents());
    }

    public function trackTime(TaskId $taskId, TrackTiming $command): void
    {
        $timing = Timing::trackTime(
            $command->timingId(),
            $command->startDateTime(),
            $command->endDateTime(),
            $command->duration(),
        );

        $this->timingRepository->save($timing);

        $this->eventDispatcher->dispatchAll($timing->releaseEvents());
    }

    public function setupWorkTimeProvider(SetupWorkTimeProvider $command): void
    {
        $workTimeProvider = WorkTimeProvider::setup(
            $command->workTimeProviderId(),
            $command->workTimeProviderType(),
            $command->credentials(),
        );

        $this->workTimeProviderRepository->save($workTimeProvider);

        $this->eventDispatcher->dispatchAll($workTimeProvider->releaseEvents());
    }

    public function downloadTasks(DownloadTasks $command): void
    {
        $workTimeProvider = $this->workTimeProviderRepository->getById($command->workTimeProviderId());
        $processor = $this->workTimeProcessorFactory->create($workTimeProvider->workTimeProviderType());

        $workTimeProvider->download($processor, $command->filterCriteria());

        $this->eventDispatcher->dispatchAll($workTimeProvider->releaseEvents());
    }
}
