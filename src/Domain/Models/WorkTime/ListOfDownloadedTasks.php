<?php

declare(strict_types=1);

namespace App\Domain\Models\WorkTime;

use App\Domain\Models\Task\ListOfTasks;
use Webmozart\Assert\Assert;

class ListOfDownloadedTasks
{
    /**
     * @var array<DownloadedTask>
     */
    private array $downloadedTasks;

    private function __construct()
    {
    }

    /**
     * @param array<DownloadedTask> $downloadedTasks
     */
    public static function fromArray(array $downloadedTasks = []): self
    {
        Assert::allIsInstanceOf($downloadedTasks, DownloadedTask::class);

        $listOfDownloadedTasks = new self();
        $listOfDownloadedTasks->downloadedTasks = $downloadedTasks;

        return $listOfDownloadedTasks;
    }

    public static function fromListOfTasks(ListOfTasks $listOfTasks): self
    {
        $listOfDownloadedTasks = new self();

        foreach ($listOfTasks->tasks() as $task) {
            $listOfDownloadedTasks->downloadedTasks[] = new DownloadedTask($task->taskId(), $task->lastModifiedAt());
        }

        return $listOfDownloadedTasks;
    }

    public function downloadedTasks(): array
    {
        return $this->downloadedTasks;
    }
}
