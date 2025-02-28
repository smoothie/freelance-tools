<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Integration\DomPdf;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Large;
use Smoothie\FreelanceTools\Domain\Model\ApprovedBy;
use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;
use Smoothie\FreelanceTools\Domain\Model\Common\Duration;
use Smoothie\FreelanceTools\Domain\Model\Group\ListOfTasksInAProject;
use Smoothie\FreelanceTools\Domain\Model\Group\ListType;
use Smoothie\FreelanceTools\Domain\Model\ProjectId;
use Smoothie\FreelanceTools\Domain\Model\ProvidedBy;
use Smoothie\FreelanceTools\Domain\Model\TimesheetReport;
use Smoothie\FreelanceTools\Domain\Model\TimesheetReportId;
use Smoothie\FreelanceTools\Infrastructure\DomPdf\DomPdfBuilder;
use Smoothie\FreelanceTools\Infrastructure\DomPdf\DomPdfComponentRenderer;
use Smoothie\FreelanceTools\Tests\Acceptance\InMemoryFilesystem;
use Smoothie\FreelanceTools\Tests\Unit\Domain\Group\GroupFactoryMethods;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[Large]
#[Group('dompdf')]
#[CoversClass(DomPdfComponentRenderer::class)]
#[CoversClass(DomPdfBuilder::class)]
class DomPdfComponentRendererTest extends KernelTestCase
{
    use GroupFactoryMethods;

    private DomPdfComponentRenderer $renderer;
    private InMemoryFilesystem $filesystem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->renderer = self::getContainer()->get(DomPdfComponentRenderer::class);
        $this->filesystem = self::getContainer()->get(InMemoryFilesystem::class);
    }

    public function testThatWeCanRenderATimesheet(): void
    {
        $project = ProjectId::fromString('some-project');
        $list = new ListOfTasksInAProject($project, ListType::DAYS);
        $list->addTask($this->aTask(5));
        $list->addTask($this->anotherTask(13));

        $assertion = new TimesheetReport(
            timesheetReportId: TimesheetReportId::fromString('c4444002-4cad-44b5-bb49-7d6e333aea5a'),
            projectId: $project,
            title: 'some title',
            approvedBy: new ApprovedBy(name: 'someone_approved', company: 'some_company'),
            approvedAt: DateTime::fromDateString('2024-12-04'),
            billedTo: 'someone_who_was_billed',
            billedBy: 'someone_who_billed',
            providedBy: new ProvidedBy('someone_who_provided_stuff', 'some_street', 'some_location'),
            startDate: DateTime::fromDateString('2024-11-01'),
            endDate: DateTime::fromDateString('2024-11-30'),
            totalDuration: Duration::fromSeconds(180),
            listOfTasks: $list,
        );

        $actual = $this->renderer->render($assertion);

        $file = $assertion->fileName('pdf');
        self::assertTrue($this->filesystem->wasFileDumped($file));
        self::assertSame($this->filesystem->getFileContents($file), $actual);
    }
}
