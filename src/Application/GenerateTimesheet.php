<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Application;

use Smoothie\FreelanceTools\Domain\Model\ApprovedBy;
use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;
use Smoothie\FreelanceTools\Domain\Model\PerformancePeriod;
use Smoothie\FreelanceTools\Domain\Model\ProjectId;
use Smoothie\FreelanceTools\Domain\Model\ProvidedBy;
use Smoothie\FreelanceTools\Domain\Model\TimesheetReportId;
use Webmozart\Assert\Assert;

class GenerateTimesheet
{
    /**
     * @param array{name: string, company: string} $approvedBy
     * @param array{name: string, street: string, location: string} $providedBy
     */
    public function __construct(
        private string $timesheetId,
        private string $project,
        private array $approvedBy,
        private array $providedBy,
        private string $performancePeriod = 'CURRENT_MONTH',
        private string $exportFormat = 'PDF',
        private ?string $startDate = null,
        private ?string $approvedAt = null,
        private ?string $billedTo = null,
        private ?string $billedBy = null,
    ) {
        Assert::notEmpty($providedBy);
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

    public function approvedBy(): ApprovedBy
    {
        Assert::keyExists($this->approvedBy, 'name');
        Assert::keyExists($this->approvedBy, 'company');

        return new ApprovedBy($this->approvedBy['name'], $this->approvedBy['company']);
    }

    public function approvedAt(): DateTime
    {
        if ($this->approvedAt === null) {
            return DateTime::fromDateTime(new \DateTimeImmutable('now'));
        }

        return DateTime::fromDateString($this->approvedAt);
    }

    public function billedTo(): string
    {
        if ($this->billedTo === null) {
            return $this->approvedBy()->company();
        }

        return $this->billedTo;
    }

    public function billedBy(): string
    {
        if ($this->billedBy === null) {
            return $this->providedBy()->name();
        }

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
        if ($this->startDate === null) {
            return DateTime::fromDateTime(new \DateTimeImmutable('now'));
        }

        return DateTime::fromDateString($this->startDate);
    }

    public function performancePeriod(): PerformancePeriod
    {
        $performancePeriod = mb_strtoupper($this->performancePeriod);
        Assert::inArray($performancePeriod, ['CURRENT_MONTH', 'LAST_MONTH']);

        return PerformancePeriod::fromString($performancePeriod);
    }
}
