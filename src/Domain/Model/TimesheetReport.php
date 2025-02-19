<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\Model\Common\DateTime;
use App\Domain\Model\Common\Duration;
use App\Domain\Model\Group\ListOfTasksInAProject;

class TimesheetReport implements Component
{
    public function __construct(
        private TimesheetReportId $timesheetReportId,
        private ProjectId $projectId,
        private string $title,
        private ApprovedBy $approvedBy,
        private DateTime $approvedAt,
        private string $billedTo,
        private string $billedBy,
        private ProvidedBy $providedBy,
        private DateTime $startDate,
        private DateTime $endDate,
        private Duration $totalDuration,
        private ListOfTasksInAProject $listOfTasks,
        private ?int $lastPageNumber = null,
    ) {
    }

    public function projectId(): ProjectId
    {
        return $this->projectId;
    }

    public function timesheetReportId(): TimesheetReportId
    {
        return $this->timesheetReportId;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function fileName(string $extension = ''): string
    {
        return \sprintf(
            '%s%s',
            implode(' - ', array_filter([
                (new \DateTimeImmutable('now'))->format('Y-m-d'),
                $this->billedTo,
                strcmp($this->billedTo, $this->approvedBy->company()) === 0
                    ? null
                    : $this->approvedBy->company(),
                str_replace('.', '-', $this->title),
                $this->billedBy,
            ])),
            empty($extension) ? '' : \sprintf('.%s', $extension),
        );
    }

    public function template(): string
    {
        return 'pdf/timesheet/report.html.twig';
    }

    public function context(): array
    {
        return [
            'timesheetReportId' => $this->timesheetReportId,
            'project' => $this->projectId,
            'title' => $this->title,
            'approvedBy' => $this->approvedBy,
            'approvedAt' => $this->approvedAt,
            'billedTo' => $this->billedTo,
            'billedBy' => $this->billedBy,
            'providedBy' => $this->providedBy,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'totalDuration' => $this->totalDuration,
            'listOfTasks' => $this->listOfTasks,
            'lastPageNumber' => $this->lastPageNumber,
        ];
    }

    public function setPageNumber(int $pageNumber): void
    {
        $this->lastPageNumber = $pageNumber;
    }
}
