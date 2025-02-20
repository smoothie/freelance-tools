<?php

declare(strict_types=1);

namespace App\Tests\Acceptance;

use App\Application\ApplicationInterface;
use App\Application\GenerateTimesheet;
use App\Domain\Model\Task;
use App\Domain\Model\Timing;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

final class TimesheetContext extends FeatureContext
{
    private ?GenerateTimesheet $command = null;

    #[Given('we have tracked some tasks with timings')]
    public function weHaveTrackedSomeTasks(): void
    {
        foreach ($this->someTasks() as $task) {
            $this->serviceContainer()->workTimeProcessor()->addTask($task);
        }
    }

    #[Given('we have prepared a command')]
    public function weHavePreparedACommand(): void
    {
        $command = new GenerateTimesheet(
            timesheetId: Uuid::uuid4()->toString(),
            project: 'cheesecake-agile',
            approvedBy: ['name' => 'Marc Eichenseher', 'company' => 'pobbd'],
            providedBy: $this->defaultProvidedBy(),
            performancePeriod: 'LAST_MONTH',
            exportFormat: 'PDF',
            startDate: '2025-01-01',
            approvedAt: '2025-02-14',
            billedTo: 'pobbd',
            billedBy: 'Marc Timite',
        );

        $this->command = $command;
    }

    #[When('we try to generate a timesheet')]
    public function weGenerateATimesheet(): void
    {
        $this->application()->generateTimesheet($this->command);
    }

    #[Then('the timesheet should have been rendered')]
    public function aTimesheetHasBeenRendered(): void
    {
        $generated = $this->serviceContainer()->componentRenderer()->generated();
        Assert::assertCount(1, $generated, 'We have assumed that we rendered exactly one timesheet');
        $generated = current($generated);
        $context = $generated['context'];
        Assert::assertSame(
            'pdf/timesheet/report.html.twig',
            $generated['template'],
            'Assumed that we render PDF for timesheet report',
        );
        Assert::assertSame(
            $this->serviceContainer()
                ->translator()
                ->trans(
                    'timesheet.title',
                    ['MM.YYYY' => '12.2024'],
                    domain: 'tools',
                ),
            $generated['title'],
            'Assumed, that the title is provided as context',
        );
        Assert::assertSame(
            $this->command->project()->asString(),
            $context['project']->asString(),
            'Assumed, that the project is provided as context',
        );

        Assert::assertNotEmpty($generated['rendered']);
    }

    #[Then('the timesheet should have been generated')]
    public function aTimesheetHasBeenGenerated(): void
    {
        $this->eventHasBeenDispatched(ApplicationInterface::EVENT_GENERATED_TIMESHEET);
    }

    private function someTasks(): array
    {
        return [
            //            new Task(
            //                projectId: 'nope-never',
            //                clientId: 'me',
            //                description: 'netflix\'n\'chill',
            //                tags: ['CHILL'],
            //                timings: [
            //                    new Timing(startTime: '2024-09-20 12:00:00', endTime: '2024-09-20 13:02:03'),
            //                ],
            //            ),
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
