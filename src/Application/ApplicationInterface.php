<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Model\Component;
use App\Domain\Model\FilterCriteria;
use App\Domain\Model\Group\ListOfTasksInProjects;
use App\Domain\Model\Invoice;
use App\Domain\Service\WorkTimeProcessor;

interface ApplicationInterface
{
    public const string EVENT_GENERATED_TIMESHEET = 'tools.generated_a_timesheet';
    public const string EVENT_GENERATED_INVOICE = 'tools.generated_an_invoice';

    public function generateTimesheet(GenerateTimesheet $command): void;

    public function generateInvoice(GenerateInvoice $command): void;

    public function buildAnEInvoice(Invoice $invoice): string;

    public function getListOfTasks(FilterCriteria $filter, WorkTimeProcessor $workTimeProcessor): ListOfTasksInProjects;

    public function renderComponent(Component $component): string;

    public function whenATimesheetReportWasGenerated(GeneratedATimesheetReport $event): void;
}
