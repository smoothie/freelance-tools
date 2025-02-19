<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Model\Common\DateTime;
use App\Domain\Model\Component;
use App\Domain\Model\FilterCriteria;
use App\Domain\Model\Group\ListOfTasksInProjects;
use App\Domain\Model\TimesheetReport;
use App\Domain\Service\ComponentRenderer;
use App\Domain\Service\WorkTimeProcessor;
use App\Infrastructure\DomPdf\DomPdfComponentRenderer;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

class Application implements ApplicationInterface
{
    public function __construct(
        private Clock $clock,
        #[Autowire(service: DomPdfComponentRenderer::class)]
        private ComponentRenderer $componentRenderer,
        private WorkTimeProcessor $workTimeProcessor,
        private EventDispatcherInterface $eventDispatcher,
        private TranslatorInterface $translator,
    ) {
    }

    public function generateTimesheet(GenerateTimesheet $command): void
    {
        $performancePeriod = $command->performancePeriod();
        $this->clock->setCurrentDate($command->startDate());
        $startDate = $this->applyDateFilter($performancePeriod->performancePeriodStartsOn());
        $endDate = $this->applyDateFilter($performancePeriod->performancePeriodEndsOn());

        $filter = $command->performancePeriod()->filterCriteria();
        $filter->withExactProject($command->project());

        $listOfTasksInProjects = $this->getListOfTasks($filter, $this->workTimeProcessor);

        Assert::count($listOfTasksInProjects->lists(), 1);
        $listOfTasks = current($listOfTasksInProjects->lists());

        $title = $this->replaceTitle($startDate);

        $report = new TimesheetReport(
            timesheetReportId: $command->timesheetId(),
            projectId: $command->project(),
            title: $title,
            approvedBy: $command->approvedBy(),
            approvedAt: $command->approvedAt(),
            billedTo: $command->billedTo(),
            billedBy: $command->billedBy(),
            providedBy: $command->providedBy(),
            startDate: $startDate,
            endDate: $endDate,
            totalDuration: $listOfTasks->totalDuration(),
            listOfTasks: $listOfTasks,
        );

        $rendered = $this->renderComponent($report);

        $this->eventDispatcher->dispatch(
            new GeneratedATimesheetReport($report, $command->exportFormat(), $rendered),
            self::EVENT_GENERATED_TIMESHEET,
        );
    }

    public function applyDateFilter(string $filter): ?DateTime
    {
        return match (mb_strtoupper($filter)) {
            'FIRST_DAY_OF_LAST_MONTH' => $this->clock->firstDayOfLastMonth(),
            'LAST_DAY_OF_LAST_MONTH' => $this->clock->lastDayOfLastMonth(),
            'FIRST_DAY_OF_THE_MONTH' => $this->clock->firstDayOfTheMonth(),
            'LAST_DAY_OF_THE_MONTH' => $this->clock->lastDayOfTheMonth(),
            default => null,
        };
    }

    public function getListOfTasks(FilterCriteria $filter, WorkTimeProcessor $workTimeProcessor): ListOfTasksInProjects
    {
        return ListOfTasksInProjects::fromTasks($workTimeProcessor->getListOfTasks($filter));
    }

    public function renderComponent(Component $component): string
    {
        return $this->componentRenderer->render($component);
    }

    private function replaceTitle(DateTime $dateTime): string
    {
        return $this->translator->trans(
            'timesheet.title',
            ['MM.YYYY' => $dateTime->asPhpDateTime()->format('m.Y')],
            domain: 'tools',
        );
    }
}
