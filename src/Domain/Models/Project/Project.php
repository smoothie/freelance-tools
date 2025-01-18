<?php

declare(strict_types=1);

namespace App\Domain\Models\Project;

use App\Domain\Models\Common\EventRecordingCapabilities;
use App\Domain\Models\Common\UsesEventRecordingCapabilities;
use App\Domain\Models\Contractor\ContractorId;
use App\Domain\Models\Contractor\CustomerId;
use App\Domain\Models\Contractor\SupplierId;
use App\Domain\Models\Task\TaskId;

class Project implements EventRecordingCapabilities
{
    use UsesEventRecordingCapabilities;

    private ProjectId $projectId;
    private ContractorId $contractorId;
    private CustomerId $customerId;
    private SupplierId $supplierId;

    private array $tasks = [];

    public function __construct()
    {
    }

    public static function assign(
        ProjectId $projectId,
        ContractorId $contractorId,
        CustomerId $customerId,
        SupplierId $supplierId,
    ): self {
        $project = new self();
        $project->projectId = $projectId;
        $project->contractorId = $contractorId;
        $project->customerId = $customerId;
        $project->supplierId = $supplierId;

        $project->events[] = new ProjectWasAssigned(
            $projectId,
            $contractorId,
            $customerId,
            $supplierId,
        );

        return $project;
    }

    public function projectId(): ProjectId
    {
        return $this->projectId;
    }

    public function contractorId(): ContractorId
    {
        return $this->contractorId;
    }

    public function customerId(): CustomerId
    {
        return $this->customerId;
    }

    public function supplierId(): SupplierId
    {
        return $this->supplierId;
    }

    public function recordTask(TaskId $taskId): void
    {
        $this->tasks[] = ProjectTask::create($this->projectId, $taskId);

        $this->events[] = new RecordedTaskForProject(
            projectId: $this->projectId,
            taskId: $taskId,
        );
    }
}
