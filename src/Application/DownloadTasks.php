<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Models\Filter\FilterCriteria;
use App\Domain\Models\WorkTime\WorkTimeProcessor;
use App\Domain\Models\WorkTime\WorkTimeProviderId;
use App\Domain\Models\WorkTime\WorkTimeProviderType;

class DownloadTasks
{
    private function __construct(
        private string $workTimeProviderId,
        private string $workTimeType,
        private string $filterCriteria,
    ) {
    }

    public function workTimeProviderId(): WorkTimeProviderId
    {
        return WorkTimeProviderId::fromString($this->workTimeProviderId);
    }

    public function workTimeType(): WorkTimeProviderType
    {
        return WorkTimeProviderType::from($this->workTimeType);
    }

    public function filterCriteria(): FilterCriteria
    {
        return FilterCriteria::fromString($this->filterCriteria);
    }
}
