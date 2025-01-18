<?php

declare(strict_types=1);

namespace App\Domain\Models\Project;

use App\Domain\Models\Contractor\ContractorId;
use App\Domain\Models\Contractor\CustomerId;
use App\Domain\Models\Contractor\SupplierId;

class ProjectWasAssigned
{
    public function __construct(
        private ProjectId $projectId,
        private ContractorId $contractorId,
        private CustomerId $customerId,
        private SupplierId $supplierId,
    ) {
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
}
