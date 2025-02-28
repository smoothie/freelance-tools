<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Unit\Domain;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use Smoothie\FreelanceTools\Domain\Model\ApprovedBy;
use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;
use Smoothie\FreelanceTools\Domain\Model\Common\Duration;
use Smoothie\FreelanceTools\Domain\Model\Component;
use Smoothie\FreelanceTools\Domain\Model\Group\ListOfTasksInAProject;
use Smoothie\FreelanceTools\Domain\Model\Group\ListType;
use Smoothie\FreelanceTools\Domain\Model\ProjectId;
use Smoothie\FreelanceTools\Domain\Model\ProvidedBy;
use Smoothie\FreelanceTools\Domain\Model\TimesheetReport;
use Smoothie\FreelanceTools\Domain\Model\TimesheetReportId;
use Smoothie\FreelanceTools\Tests\BasicTestCase;

#[Small]
#[Group('domain')]
#[Group('timesheet')]
#[Group('report')]
#[CoversClass(TimesheetReport::class)]
#[CoversClass(TimesheetReportId::class)]
#[CoversClass(ApprovedBy::class)]
#[CoversClass(ProvidedBy::class)]
#[CoversClass(ProjectId::class)]
#[UsesClass(ListOfTasksInAProject::class)]
#[UsesClass(DateTime::class)]
#[UsesClass(Duration::class)]
class TimesheetReportTest extends BasicTestCase
{
    #[DataProvider('provideGoodCase')]
    public function testThatWeCanQueryForPerformancePeriods(array $assertions, array $expectation): void
    {
        $report = $assertions['report'];
        $project = ProjectId::fromString($report['projectId']);
        $reportId = TimesheetReportId::fromString($report['timesheetReportId']);
        $listOfTasks = new ListOfTasksInAProject($project, ListType::DAYS);
        $approvedBy = new ApprovedBy(...$report['approvedBy']);
        $approvedAt = DateTime::fromDateString($report['approvedAt']);
        $providedBy = new ProvidedBy(...$report['providedBy']);
        $startDate = DateTime::fromDateString($report['startDate']);
        $endDate = DateTime::fromDateString($report['endDate']);
        $totalDuration = Duration::fromSeconds($report['totalDuration']);

        $actual = new TimesheetReport(
            timesheetReportId: $reportId,
            projectId: $project,
            title: $report['title'],
            approvedBy: $approvedBy,
            approvedAt: $approvedAt,
            billedTo: $report['billedTo'],
            billedBy: $report['billedBy'],
            providedBy: $providedBy,
            startDate: $startDate,
            endDate: $endDate,
            totalDuration: $totalDuration,
            listOfTasks: $listOfTasks,
            lastPageNumber: $report['lastPageNumber'],
        );

        self::assertInstanceOf(Component::class, $actual);
        self::assertSame(
            $expectation['fileName'],
            $actual->fileName($assertions['extension'], DateTime::fromDateString('2025-02-19')),
        );
        self::assertSame($expectation['timesheetReportId'], $actual->timesheetReportId()->asString());
        self::assertSame($expectation['projectId'], $actual->projectId()->asString());
        self::assertSame($expectation['title'], $actual->title());
        self::assertSame($expectation['template'], $actual->template());

        self::assertEqualsCanonicalizing([
            'timesheetReportId' => $reportId,
            'project' => $project,
            'title' => $report['title'],
            'approvedBy' => $approvedBy,
            'approvedAt' => $approvedAt,
            'billedTo' => $report['billedTo'],
            'billedBy' => $report['billedBy'],
            'providedBy' => $providedBy,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalDuration' => $totalDuration,
            'listOfTasks' => $listOfTasks,
            'lastPageNumber' => $report['lastPageNumber'],
        ], $actual->context());
    }

    public static function provideGoodCase(): \Generator
    {
        $context = [
            'timesheetReportId' => 'c4444002-4cad-44b5-bb49-7d6e333aea5a',
            'projectId' => 'someProject',
            'title' => 'Some Title',
            'approvedBy' => ['name' => 'approved_by', 'company' => 'approved_by_company'],
            'approvedAt' => '2025-01-04',
            'billedTo' => 'billed_to',
            'billedBy' => 'billed_by',
            'providedBy' => [
                'name' => 'provided_by',
                'street' => 'provided_by_address',
                'location' => 'provided_by_location',
            ],
            'startDate' => '2024-12-01',
            'endDate' => '2024-12-31',
            'totalDuration' => 5,
            'lastPageNumber' => null,
        ];
        yield 'default' => [
            'assertions' => [
                'extension' => 'woot',
                'report' => $context,
            ],
            'expectation' => [
                'fileName' => '2025-02-19 - billed_to - approved_by_company - Some Title - billed_by.woot',
                'timesheetReportId' => 'c4444002-4cad-44b5-bb49-7d6e333aea5a',
                'projectId' => 'someProject',
                'title' => 'Some Title',
                'template' => 'pdf/timesheet/report.html.twig',
            ],
        ];

        $context['billedTo'] = 'approved_by_company';

        yield 'assume_only_one_name_in_file_when_billed_to_and_approved_by_same' => [
            'assertions' => [
                'extension' => 'woot',
                'report' => $context,
            ],
            'expectation' => [
                'fileName' => '2025-02-19 - approved_by_company - Some Title - billed_by.woot',
                'timesheetReportId' => 'c4444002-4cad-44b5-bb49-7d6e333aea5a',
                'projectId' => 'someProject',
                'title' => 'Some Title',
                'template' => 'pdf/timesheet/report.html.twig',
            ],
        ];
    }
}
