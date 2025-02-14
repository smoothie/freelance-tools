<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Models\Task\TaskId;
use App\Domain\Models\WorkTime\WorkTimeProcessor;
use App\Domain\Models\WorkTime\WorkTimeProvider;
use App\Domain\Models\WorkTime\WorkTimeProviderId;

interface ApplicationInterface
{
    public function importATask(ImportTask $command): void;

    public function trackTime(TaskId $taskId, TrackTiming $command): void;

    public function setupWorkTimeProvider(SetupWorkTimeProvider $command): void;

    public function downloadTasks(DownloadTasks $command): void;

    //    public function render(Component $component): void;
}
