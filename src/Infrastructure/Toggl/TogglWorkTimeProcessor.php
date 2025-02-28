<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Infrastructure\Toggl;

use Smoothie\FreelanceTools\Domain\Model\FilterCriteria;
use Smoothie\FreelanceTools\Domain\Service\WorkTimeProcessor;

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
