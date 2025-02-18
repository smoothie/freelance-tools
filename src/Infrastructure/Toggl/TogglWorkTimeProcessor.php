<?php

declare(strict_types=1);

namespace App\Infrastructure\Toggl;

use App\Domain\Model\FilterCriteria;
use App\Domain\Service\WorkTimeProcessor;

class TogglWorkTimeProcessor implements WorkTimeProcessor
{
    public function __construct(private TogglReportClient $togglReportClient)
    {
    }

    public function getListOfTasks(FilterCriteria $filter): array
    {
        return $this->togglReportClient->findTimeEntries($filter);
    }
}
