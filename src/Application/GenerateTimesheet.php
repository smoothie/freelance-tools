<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Model\Common\DateTime;
use App\Domain\Model\PerformancePeriod;
use App\Domain\Model\ProjectId;
use App\Domain\Model\ProvidedBy;
use App\Domain\Model\TimesheetReportId;
use Webmozart\Assert\Assert;

class GenerateTimesheet
{
    public function __construct(
        private string $timesheetId,
        private string $exportFormat,
        private string $project,
        private string $title,
        private string $approvedBy,
        private string $approvedAt,
        private string $billedTo,
        private string $billedBy,
        private string $startDate,
        private string $performancePeriod,
        private array $providedBy = [],
    ) {
    }

    public function timesheetId(): TimesheetReportId
    {
        return TimesheetReportId::fromString($this->timesheetId);
    }

    public function exportFormat(): string
    {
        $exportFormat = mb_strtoupper($this->exportFormat);
        Assert::inArray($exportFormat, ['PDF']);

        return $exportFormat;
    }

    public function project(): ProjectId
    {
        return ProjectId::fromString($this->project);
    }

    public function title(): string
    {
        return $this->title;
    }

    public function approvedBy(): string
    {
        return $this->approvedBy;
    }

    public function approvedAt(): DateTime
    {
        return DateTime::fromDateString($this->approvedAt);
    }

    public function billedTo(): string
    {
        return $this->billedTo;
    }

    public function billedBy(): string
    {
        return $this->billedBy;
    }

    public function providedBy(): ProvidedBy
    {
        Assert::keyExists($this->providedBy, 'name');
        Assert::keyExists($this->providedBy, 'street');
        Assert::keyExists($this->providedBy, 'location');

        return new ProvidedBy($this->providedBy['name'], $this->providedBy['street'], $this->providedBy['location']);
    }

    public function startDate(): DateTime
    {
        return DateTime::fromDateString($this->startDate);
    }

    public function performancePeriod(): PerformancePeriod
    {
        $performancePeriod = mb_strtoupper($this->performancePeriod);
        Assert::inArray($performancePeriod, ['CURRENT_MONTH', 'LAST_MONTH']);

        return PerformancePeriod::fromString($performancePeriod);
    }
}
