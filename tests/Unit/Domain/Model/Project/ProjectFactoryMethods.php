<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Project;

use App\Domain\Models\Contractor\ContractorId;
use App\Domain\Models\Contractor\CustomerId;
use App\Domain\Models\Contractor\SupplierId;
use App\Domain\Models\Project\Project;
use App\Domain\Models\Project\ProjectId;
use App\Domain\Models\Task\TaskId;
use Ramsey\Uuid\Uuid;

trait ProjectFactoryMethods
{
    protected function aProjectId(): ProjectId
    {
        return ProjectId::fromString('6e87f68c-0000-0000-0000-000000000000');
    }

    protected function aTaskId(): TaskId
    {
        return TaskId::fromString('0abc4e01-0000-0000-0000-000000000000');
    }

    protected function aTimingId(): TaskId
    {
        return TaskId::fromString('0abc4e01-0000-0000-0000-000000000000');
    }

    protected function aContractorId(): ContractorId
    {
        return ContractorId::fromString('33cf09d4-0000-0000-0000-000000000000');
    }

    protected function aCustomerId(): CustomerId
    {
        return CustomerId::fromString('b8d2ea3d-0000-0000-0000-000000000000');
    }

    protected function aSupplierId(): SupplierId
    {
        return SupplierId::fromString('198cf886-0000-0000-0000-000000000000');
    }

    protected function aRandomProjectId(): ProjectId
    {
        return ProjectId::fromString(Uuid::uuid4()->toString());
    }

    protected function aRandomContractorId(): ContractorId
    {
        return ContractorId::fromString(Uuid::uuid4()->toString());
    }

    protected function aRandomCustomerId(): CustomerId
    {
        return CustomerId::fromString(Uuid::uuid4()->toString());
    }

    protected function aRandomSupplierId(): SupplierId
    {
        return SupplierId::fromString(Uuid::uuid4()->toString());
    }

    private function aProject(): Project
    {
        $project = Project::assign(
            projectId: $this->aProjectId(),
            contractorId: $this->aContractorId(),
            customerId: $this->aCustomerId(),
            supplierId: $this->aSupplierId(),
        );

        $project->releaseEvents();

        return $project;
    }
}
