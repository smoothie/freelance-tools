<?php

declare(strict_types=1);

namespace App\Domain\Models\Timesheet\Template;

use App\Domain\Models\Common\EventRecordingCapabilities;
use App\Domain\Models\Common\UsesEventRecordingCapabilities;
use App\Domain\Models\Filter\FilterCriteria;
use App\Domain\Models\Grouping\GroupingCriteria;
use App\Domain\Models\Pattern\TitlePattern;
use App\Domain\Models\Timesheet\Report\TimesheetReportGenerator;
use App\Domain\Models\Timesheet\Template\ApprovedBy\ApprovedById;
use App\Domain\Models\Timesheet\Template\BilledTo\BilledToId;
use App\Domain\Models\Timesheet\Template\PerformancePeriod\PerformancePeriodId;
use App\Domain\Models\Timesheet\Template\ProvidedBy\ProvidedById;

class TimesheetTemplate implements EventRecordingCapabilities
{
    use UsesEventRecordingCapabilities;

    private TimesheetTemplateId $timesheetTemplateId;
    private TitlePattern $titlePattern;
    private ApprovedById $approvedById;
    private BilledToId $billedToId;
    private ProvidedById $providedById;
    private PerformancePeriodId $performancePeriodId;
    private ListOfWorkTimeProviderIdsForImport $listOfWorkTimeProviderIdsForImport;
    private ListOfExportFormats $listOfExportFormats;
    private ?FilterCriteria $filterCriteria = null;
    private ?GroupingCriteria $grouping = null;

    public function __construct()
    {
    }

    public static function draft(
        TimesheetTemplateId $timesheetTemplateId,
        TitlePattern $titlePattern,
        ApprovedById $approvedById,
        BilledToId $billedToId,
        ProvidedById $providedById,
        PerformancePeriodId $performancePeriodId,
        FilterCriteria $filterCriteria,
        GroupingCriteria $grouping,
        ListOfWorkTimeProviderIdsForImport $listOfWorkTimeProviderIdsForImport,
        ListOfExportFormats $listOfExportFormats,
    ): self {
        $template = new self();
        $template->timesheetTemplateId = $timesheetTemplateId;
        $template->titlePattern = $titlePattern;
        $template->approvedById = $approvedById;
        $template->billedToId = $billedToId;
        $template->providedById = $providedById;
        $template->performancePeriodId = $performancePeriodId;
        $template->filterCriteria = $filterCriteria;
        $template->grouping = $grouping;
        $template->listOfWorkTimeProviderIdsForImport = $listOfWorkTimeProviderIdsForImport;
        $template->listOfExportFormats = $listOfExportFormats;

        $template->events[] = new TimesheetTemplateWasDrafted(
            timesheetTemplateId: $template->timesheetTemplateId,
        );

        return $template;
    }

    public function timesheetTemplateId(): TimesheetTemplateId
    {
        return $this->timesheetTemplateId;
    }

    public function titlePattern(): TitlePattern
    {
        return $this->titlePattern;
    }

    public function approvedById(): ApprovedById
    {
        return $this->approvedById;
    }

    public function billedToId(): BilledToId
    {
        return $this->billedToId;
    }

    public function providedById(): ProvidedById
    {
        return $this->providedById;
    }

    public function performancePeriodId(): PerformancePeriodId
    {
        return $this->performancePeriodId;
    }

    public function listOfWorkTimeProviderIdsForImport(): ListOfWorkTimeProviderIdsForImport
    {
        return $this->listOfWorkTimeProviderIdsForImport;
    }

    public function listOfExportFormats(): ListOfExportFormats
    {
        return $this->listOfExportFormats;
    }

    public function filterCriteria(): FilterCriteria
    {
        return $this->filterCriteria;
    }

    public function grouping(): GroupingCriteria
    {
        return $this->grouping;
    }

    public function changeTitlePattern(TitlePattern $titlePattern): void
    {
        $oldPattern = $this->titlePattern;
        $this->titlePattern = $titlePattern;

        $this->events[] = new ChangedTitlePatternOnTimesheetTemplate(
            timesheetTemplateId: $this->timesheetTemplateId,
            new: $this->titlePattern,
            old: $oldPattern,
        );
    }

    public function changeFilter(FilterCriteria $filterCriteria): void
    {
        $oldFilter = $this->filterCriteria;
        $this->filterCriteria = $filterCriteria;

        $this->events[] = new ChangedFilterOnTimesheetTemplate(
            timesheetTemplateId: $this->timesheetTemplateId,
            new: $this->filterCriteria,
            old: $oldFilter,
        );
    }

    public function changeGrouping(GroupingCriteria $grouping): void
    {
        $oldFilter = $this->grouping;
        $this->grouping = $grouping;

        $this->events[] = new ChangedGroupingOnTimesheetTemplate(
            timesheetTemplateId: $this->timesheetTemplateId,
            new: $this->grouping,
            old: $oldFilter,
        );
    }

    public function delete(): void
    {
        $this->events[] = new DeletedATimesheetTemplate(
            timesheetTemplateId: $this->timesheetTemplateId,
        );
    }

    public function generateReport(TimesheetReportGenerator $reportGenerator): void
    {
        $report = $reportGenerator->generate($this);

        $this->events[] = new GeneratedAReportForTimesheetTemplate(
            templateId: $this->timesheetTemplateId,
            report: $report,
        );
    }
}
