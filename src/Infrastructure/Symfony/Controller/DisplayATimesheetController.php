<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Infrastructure\Symfony\Controller;

use Smoothie\FreelanceTools\Domain\Model\ApprovedBy;
use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;
use Smoothie\FreelanceTools\Domain\Model\Group\ListOfTasksInAProject;
use Smoothie\FreelanceTools\Domain\Model\Group\ListType;
use Smoothie\FreelanceTools\Domain\Model\ProjectId;
use Smoothie\FreelanceTools\Domain\Model\ProvidedBy;
use Smoothie\FreelanceTools\Domain\Model\Task;
use Smoothie\FreelanceTools\Domain\Model\TimesheetReport;
use Smoothie\FreelanceTools\Domain\Model\TimesheetReportId;
use Smoothie\FreelanceTools\Domain\Model\Timing;
use Smoothie\FreelanceTools\Infrastructure\DomPdf\DomPdfBuilder;
use Smoothie\FreelanceTools\Infrastructure\Symfony\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[When('dev')]
final class DisplayATimesheetController extends AbstractController
{
    public function __construct(
        private DomPdfBuilder $domPdfBuilder,
        private Filesystem $filesystem,
        private Environment $twig,
    ) {
    }

    #[Route('/timesheet', name: 'tools.display_timesheet')]
    public function __invoke(): Response
    {
        $context = $this->aReport()->context();
        $html = $this->twig->render('pdf/timesheet/report.html.twig', $context);

        $pdf = $this->domPdfBuilder->initialize('some')
            ->load($html)
            ->build();

        $this->filesystem->dumpExport(file: 'trying/report.pdf', content: $pdf);

        return new Response($pdf, Response::HTTP_OK, ['Content-Type' => 'application/pdf']);
    }

    private function aReport(): TimesheetReport
    {
        $projectId = ProjectId::fromString('cheesecake-agile');

        $listOfTasks = new ListOfTasksInAProject($projectId, ListType::DAYS);
        foreach ($this->someTasks() as $task) {
            $listOfTasks->addTask($task);
        }

        $timesheet = new TimesheetReport(
            timesheetReportId: TimesheetReportId::fromString('c4444002-4cad-44b5-bb49-7d6e333aea5a'),
            projectId: $projectId,
            title: 'some-title',
            approvedBy: new ApprovedBy('Marc Eichenseher', 'Some company'),
            approvedAt: DateTime::fromDateString('2025-01-01'),
            billedTo: 'Some BilledTo',
            billedBy: 'Some Company',
            providedBy: new ProvidedBy('Some consultant', 'some street', 'some location'),
            startDate: DateTime::fromDateString('2024-12-01'),
            endDate: DateTime::fromDateString('2024-12-31'),
            totalDuration: $listOfTasks->totalDuration(),
            listOfTasks: $listOfTasks,
            lastPageNumber: 1,
        );

        return $timesheet;
    }

    /**
     * @return Task[]
     */
    private function someTasks(): array
    {
        return [
            new Task(
                projectId: 'cheesecake-agile',
                clientId: 'me',
                description: 'Get the groceries',
                tags: ['PREP'],
                timings: [
                    new Timing(startTime: '2024-09-17 12:00:00', endTime: '2024-09-17 12:07:02'),
                    new Timing(startTime: '2024-09-18 12:00:00', endTime: '2024-09-18 12:27:02'),
                    new Timing(startTime: '2024-09-18 14:00:00', endTime: '2024-09-18 15:00:00'),
                ],
            ),
            new Task(
                projectId: 'cheesecake-agile',
                clientId: 'me',
                description: 'Prepare working environment',
                tags: ['PREP'],
                timings: [
                    new Timing(startTime: '2024-09-19 11:45:17', endTime: '2024-09-19 12:05:20'),
                ],
            ),
            new Task(
                projectId: 'cheesecake-agile',
                clientId: 'me',
                description: 'Prepare the crust',
                tags: ['BAKE'],
                timings: [
                    new Timing(startTime: '2024-09-19 12:10:00', endTime: '2024-09-19 12:25:05'),
                ],
            ),
            new Task(
                projectId: 'cheesecake-agile',
                clientId: 'me',
                description: 'Make the cheesecake filling',
                tags: ['BAKE'],
                timings: [
                    new Timing(startTime: '2024-09-18 12:30:00', endTime: '2024-09-18 12:45:00'),
                ],
            ),
            new Task(
                projectId: 'cheesecake-agile',
                clientId: 'me',
                description: 'Assemble the cheesecake',
                tags: ['BAKE'],
                timings: [
                    new Timing(startTime: '2024-09-18 12:45:00', endTime: '2024-09-18 13:00:00'),
                ],
            ),
            new Task(
                projectId: 'cheesecake-agile',
                clientId: 'me',
                description: 'Let it bake',
                tags: ['BAKE', 'CHILL'],
                timings: [
                    new Timing(startTime: '2024-09-18 12:00:00', endTime: '2024-09-18 14:05:00'),
                ],
            ),
            new Task(
                projectId: 'cheesecake-agile',
                clientId: 'me',
                description: 'Cool and chill',
                tags: ['CHILL'],
                timings: [
                    new Timing(startTime: '2024-09-18 14:05:00', endTime: '2024-09-18 18:00:00'),
                ],
            ),
        ];
    }
}
