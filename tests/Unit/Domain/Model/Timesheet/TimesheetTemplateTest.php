<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Timesheet;

use App\Domain\Models\Timesheet\Template\ChangedFilterOnTimesheetTemplate;
use App\Domain\Models\Timesheet\Template\ChangedGroupingOnTimesheetTemplate;
use App\Domain\Models\Timesheet\Template\ChangedTitlePatternOnTimesheetTemplate;
use App\Domain\Models\Timesheet\Template\DeletedATimesheetTemplate;
use App\Domain\Models\Timesheet\Template\GeneratedAReportForTimesheetTemplate;
use App\Domain\Models\Timesheet\Template\TimesheetTemplate;
use App\Domain\Models\Timesheet\Template\TimesheetTemplateWasDrafted;
use App\Tests\BasicTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

#[Small]
#[Group('domain')]
#[Group('model')]
#[Group('timesheet')]
#[Group('timesheet-template')]
#[CoversClass(TimesheetTemplate::class)]
class TimesheetTemplateTest extends BasicTestCase
{
    use TimesheetFactoryMethods;

    public function testWeCanSetupAWorkTimeProvider(): void
    {
        $aTimesheetTemplateId = $this->aTimesheetTemplateId();
        $aTitlePattern = $this->aTitlePattern();
        $anApprovedById = $this->anApprovedById();
        $aBilledToId = $this->aBilledToId();
        $aProvidedById = $this->aProvidedById();
        $aPerformancePeriodId = $this->aPerformancePeriodId();
        $aFilter = $this->aFilterCriteria();
        $aGrouping = $this->aGroupingCriteria();
        $aListOfWorkTimeProviderIdsForImport = $this->aListOfWorkTimeProviderIdsForImport();
        $aListOfExportFormats = $this->aListOfExportFormats();

        $template = TimesheetTemplate::draft(
            timesheetTemplateId: $aTimesheetTemplateId,
            titlePattern: $aTitlePattern,
            approvedById: $anApprovedById,
            billedToId: $aBilledToId,
            providedById: $aProvidedById,
            performancePeriodId: $aPerformancePeriodId,
            filterCriteria: $aFilter,
            grouping: $aGrouping,
            listOfWorkTimeProviderIdsForImport: $aListOfWorkTimeProviderIdsForImport,
            listOfExportFormats: $aListOfExportFormats,
        );

        self::assertArrayContainsObjectOfType(TimesheetTemplateWasDrafted::class, $template->releaseEvents());

        self::assertSame($aTimesheetTemplateId, $template->timesheetTemplateId());
        self::assertSame($aTitlePattern, $template->titlePattern());
        self::assertSame($anApprovedById, $template->approvedById());
        self::assertSame($aBilledToId, $template->billedToId());
        self::assertSame($aProvidedById, $template->providedById());
        self::assertSame($aPerformancePeriodId, $template->performancePeriodId());
        self::assertSame($aFilter, $template->filterCriteria());
        self::assertSame($aGrouping, $template->grouping());
        self::assertSame($aListOfWorkTimeProviderIdsForImport, $template->listOfWorkTimeProviderIdsForImport());
        self::assertSame($aListOfExportFormats, $template->listOfExportFormats());
    }

    public function testWeCanChangeATitlePattern(): void
    {
        $template = $this->aTimesheetTemplate();
        $aRandomTitlePattern = $this->anotherTitlePattern();

        $template->changeTitlePattern($aRandomTitlePattern);

        self::assertArrayContainsObjectOfType(ChangedTitlePatternOnTimesheetTemplate::class, $template->releaseEvents());

        self::assertSame($aRandomTitlePattern, $template->titlePattern());
    }

    public function testWeCanChangeAFilter(): void
    {
        $template = $this->aTimesheetTemplate();
        $aFilterCriteria = $this->aFilterCriteria();

        $template->changeFilter($aFilterCriteria);

        self::assertArrayContainsObjectOfType(ChangedFilterOnTimesheetTemplate::class, $template->releaseEvents());

        self::assertSame($aFilterCriteria, $template->filterCriteria());
    }

    public function testWeCanChangeAGroup(): void
    {
        $template = $this->aTimesheetTemplate();
        $aGroupingCriteria = $this->aGroupingCriteria();

        $template->changeGrouping($aGroupingCriteria);

        self::assertArrayContainsObjectOfType(ChangedGroupingOnTimesheetTemplate::class, $template->releaseEvents());

        self::assertSame($aGroupingCriteria, $template->grouping());
    }

    public function testWeCanDelete(): void
    {
        $template = $this->aTimesheetTemplate();

        $template->delete();

        self::assertArrayContainsObjectOfType(DeletedATimesheetTemplate::class, $template->releaseEvents());
    }

    public function testWeCanGenerateReport(): void
    {
        $template = $this->aTimesheetTemplate();
        $generator = $this->aTimesheetReportGenerator();

        $template->generateReport($generator);

        self::assertArrayContainsObjectOfType(GeneratedAReportForTimesheetTemplate::class, $template->releaseEvents());
    }
}
