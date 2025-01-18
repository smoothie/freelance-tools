<?php

declare(strict_types=1);

namespace App\Domain\Models\WorkTime;

class DownloadedTasksForWorkTimeProvider
{
    public function __construct(
        private WorkTimeProviderId $workTimeProviderId,
        private ListOfDownloadedTasks $listOfDownloadedTasks,
    ) {
    }

    public function workTimeProviderId(): WorkTimeProviderId
    {
        return $this->workTimeProviderId;
    }

    public function listOfDownloadedTasks(): ListOfDownloadedTasks
    {
        return $this->listOfDownloadedTasks;
    }
}
