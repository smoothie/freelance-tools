<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Timesheet;

use App\Domain\Models\Filter\FilterCriteria;
use App\Domain\Models\Grouping\GroupingCriteria;
use App\Domain\Models\Pattern\TitlePattern;
use App\Domain\Models\Timesheet\ExportFormat;
use App\Domain\Models\Timesheet\Report\TimesheetReport;
use App\Domain\Models\Timesheet\Report\TimesheetReportGenerator;
use App\Domain\Models\Timesheet\Report\TimesheetReportId;
use App\Domain\Models\Timesheet\Template\ApprovedAt;
use App\Domain\Models\Timesheet\Template\ApprovedBy\ApprovedById;
use App\Domain\Models\Timesheet\Template\BilledTo\BilledToId;
use App\Domain\Models\Timesheet\Template\ListOfExportFormats;
use App\Domain\Models\Timesheet\Template\ListOfWorkTimeProviderIdsForImport;
use App\Domain\Models\Timesheet\Template\PerformancePeriod\PerformancePeriodId;
use App\Domain\Models\Timesheet\Template\ProvidedBy\ProvidedById;
use App\Domain\Models\Timesheet\Template\TimesheetTemplate;
use App\Domain\Models\Timesheet\Template\TimesheetTemplateId;
use App\Domain\Models\WorkTime\WorkTimeProviderId;

trait TimesheetFactoryMethods
{
    protected function aTimesheetTemplateId(): TimesheetTemplateId
    {
        return TimesheetTemplateId::fromString('da111111-0000-0000-0000-000000000000');
    }

    protected function aTitlePattern(): TitlePattern
    {
        return TitlePattern::fromString('A title pattern');
    }

    protected function anotherTitlePattern(): TitlePattern
    {
        return TitlePattern::fromString('another title pattern');
    }

    protected function anApprovedAt(): ApprovedAt
    {
        return ApprovedAt::fromString('2024-12-31 11:00:00');
    }

    protected function anApprovedById(): ApprovedById
    {
        return ApprovedById::fromString('aa111111-0000-0000-0000-000000000000');
    }

    protected function aBilledToId(): BilledToId
    {
        return BilledToId::fromString('ba111111-0000-0000-0000-000000000000');
    }

    protected function aProvidedById(): ProvidedById
    {
        return ProvidedById::fromString('fa111111-0000-0000-0000-000000000000');
    }

    protected function aPerformancePeriodId(): PerformancePeriodId
    {
        return PerformancePeriodId::fromString('ea111111-0000-0000-0000-000000000000');
    }

    protected function aWorkTimeProviderId(): WorkTimeProviderId
    {
        return WorkTimeProviderId::fromString('ca111111-0000-0000-0000-000000000000');
    }

    protected function anotherWorkTimeProviderId(): WorkTimeProviderId
    {
        return WorkTimeProviderId::fromString('ca222222-0000-0000-0000-000000000000');
    }

    protected function aListOfWorkTimeProviderIdsForImport(): ListOfWorkTimeProviderIdsForImport
    {
        $providerIds = [
            $this->aWorkTimeProviderId(),
            $this->anotherWorkTimeProviderId(),
        ];

        return ListOfWorkTimeProviderIdsForImport::fromArray($providerIds);
    }

    protected function aExportFormat(): ExportFormat
    {
        return new ExportFormat('A_FORMAT');
    }

    protected function anotherExportFormat(): ExportFormat
    {
        return new ExportFormat('ANOTHER_FORMAT');
    }

    protected function aListOfExportFormats(): ListOfExportFormats
    {
        $exportFormats = [
            $this->aExportFormat(),
            $this->anotherExportFormat(),
        ];

        return ListOfExportFormats::fromArray($exportFormats);
    }

    protected function aFilterCriteria(): FilterCriteria
    {
        return FilterCriteria::fromString('project = a');
    }

    protected function aGroupingCriteria(): GroupingCriteria
    {
        return GroupingCriteria::fromString('tasks.IN_A_DAY');
    }

    protected function aTimesheetTemplate(): TimesheetTemplate
    {
        return TimesheetTemplate::draft(
            timesheetTemplateId: $this->aTimesheetTemplateId(),
            titlePattern: $this->aTitlePattern(),
            approvedById: $this->anApprovedById(),
            billedToId: $this->aBilledToId(),
            providedById: $this->aProvidedById(),
            performancePeriodId: $this->aPerformancePeriodId(),
            filterCriteria: $this->aFilterCriteria(),
            grouping: $this->aGroupingCriteria(),
            listOfWorkTimeProviderIdsForImport: $this->aListOfWorkTimeProviderIdsForImport(),
            listOfExportFormats: $this->aListOfExportFormats(),
        );
    }

    protected function aTimesheetReportId(): TimesheetReportId
    {
        return TimesheetReportId::fromString('da222222-0000-0000-0000-000000000000');
    }

    protected function aTimesheetReport(): TimesheetReport
    {
        return TimesheetReport::record(
            timesheetReportId: $this->aTimesheetReportId(),
        );
    }

    protected function aTimesheetReportGenerator(): TimesheetReportGenerator
    {
        $report = $this->aTimesheetReport();

        return new class($report) implements TimesheetReportGenerator {
            public function __construct(private TimesheetReport $report)
            {
            }

            public function generate(TimesheetTemplate $template): TimesheetReport
            {
                return $this->report;
            }
        };
    }
}
