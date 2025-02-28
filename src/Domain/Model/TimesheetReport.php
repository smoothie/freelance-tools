<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Domain\Model;

use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;
use Smoothie\FreelanceTools\Domain\Model\Common\Duration;
use Smoothie\FreelanceTools\Domain\Model\Group\ListOfTasksInAProject;

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

    public function totalDuration(): Duration
    {
        return $this->totalDuration;
    }

    public function endDate(): DateTime
    {
        return $this->endDate;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function fileName(string $extension = '', ?DateTime $now = null): string
    {
        if ($now === null) {
            $now = DateTime::fromDateTime(new \DateTimeImmutable('now'));
        }

        return \sprintf(
            '%s%s',
            implode(' - ', array_filter([
                $now->asDateString(),
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

    /**
     * @return array{timesheetReportId: TimesheetReportId, project: ProjectId, title: string, approvedBy: ApprovedBy, approvedAt: DateTime, billedTo: string, billedBy: string, providedBy: ProvidedBy, startDate: DateTime, endDate: DateTime, totalDuration: Duration, listOfTasks: ListOfTasksInAProject,  lastPageNumber: ?int}
     */
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
