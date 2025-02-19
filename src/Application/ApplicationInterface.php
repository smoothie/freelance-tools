<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Model\Component;
use App\Domain\Model\FilterCriteria;
use App\Domain\Model\Group\ListOfTasksInProjects;
use App\Domain\Service\WorkTimeProcessor;

interface ApplicationInterface
{
    public const string EVENT_GENERATED_TIMESHEET = 'tools.generated_a_timesheet';

    public function generateTimesheet(GenerateTimesheet $command): void;

    public function getListOfTasks(FilterCriteria $filter, WorkTimeProcessor $workTimeProcessor): ListOfTasksInProjects;

    public function renderComponent(Component $component): string;
}
