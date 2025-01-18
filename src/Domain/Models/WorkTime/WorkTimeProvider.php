<?php

declare(strict_types=1);

namespace App\Domain\Models\WorkTime;

use App\Domain\Models\Common\EventRecordingCapabilities;
use App\Domain\Models\Common\UsesEventRecordingCapabilities;
use App\Domain\Models\Filter\FilterCriteria;
use App\Domain\Models\WorkTime\Credentials\Credentials;

class WorkTimeProvider implements EventRecordingCapabilities
{
    use UsesEventRecordingCapabilities;

    private WorkTimeProviderId $workTimeProviderId;
    private WorkTimeProviderType $workTimeProviderType;
    private Credentials $credentials;

    private function __construct()
    {
    }

    public static function setup(
        WorkTimeProviderId $workTimeProviderId,
        WorkTimeProviderType $workTimeProviderType,
        Credentials $credentials,
    ): self {
        $workTimeProvider = new self();
        $workTimeProvider->workTimeProviderId = $workTimeProviderId;
        $workTimeProvider->workTimeProviderType = $workTimeProviderType;
        $workTimeProvider->credentials = $credentials;

        $workTimeProvider->events[] = new WorkTimeProviderWasSetup(
            $workTimeProviderId,
            $workTimeProviderType,
        );

        return $workTimeProvider;
    }

    public function workTimeProviderId(): WorkTimeProviderId
    {
        return $this->workTimeProviderId;
    }

    public function workTimeProviderType(): WorkTimeProviderType
    {
        return $this->workTimeProviderType;
    }

    public function credentials(): Credentials
    {
        return $this->credentials;
    }

    public function download(WorkTimeProcessor $processor, FilterCriteria $filterCriteria): void
    {
        $listOfTasks = $processor->getListOfTasks($filterCriteria, $this->credentials());

        $this->events[] = new DownloadedTasksForWorkTimeProvider(
            workTimeProviderId: $this->workTimeProviderId,
            listOfDownloadedTasks: ListOfDownloadedTasks::fromListOfTasks($listOfTasks),
        );
    }
}
