<?php

declare(strict_types=1);

namespace App\Domain\Models\Timesheet\Report;

use App\Domain\Models\Filter\FilterCriteria;
use App\Domain\Models\Grouping\GroupingCriteria;
use App\Domain\Models\Task\ListOfTasks;
use Webmozart\Assert\Assert;

class ListOfFilteredAndGroupedTasks
{
    private FilterCriteria $filterCriteria;
    private GroupingCriteria $groupingCriteria;

    /**
     * @var array<FilteredAndGroupedTask>
     */
    private array $filteredAndGroupedTasks;

    private function __construct()
    {
    }

    /**
     * @param array<FilteredAndGroupedTask> $filteredAndGroupedTasks
     */
    public static function fromArray(FilterCriteria $filterCriteria, GroupingCriteria $groupingCriteria, array $filteredAndGroupedTasks): self
    {
        Assert::allIsInstanceOf($filteredAndGroupedTasks, FilteredAndGroupedTask::class);

        $listOfFilteredAndGroupedTasks = new self();
        $listOfFilteredAndGroupedTasks->filterCriteria = $filterCriteria;
        $listOfFilteredAndGroupedTasks->groupingCriteria = $groupingCriteria;
        $listOfFilteredAndGroupedTasks->filteredAndGroupedTasks = $filteredAndGroupedTasks;

        return $listOfFilteredAndGroupedTasks;
    }

    public static function fromListOfTasks(ListOfTasks $listOfTasks): self
    {
        $listOfFilteredAndGroupedTasks = new self();

        foreach ($listOfTasks->tasks() as $task) {
            $listOfFilteredAndGroupedTasks->filteredAndGroupedTasks[] = new FilteredAndGroupedTask($task->taskId(), $task->lastModifiedAt());
        }

        return $listOfFilteredAndGroupedTasks;
    }

    public function filteredAndGroupedTasks(): array
    {
        return $this->filteredAndGroupedTasks;
    }
}
