<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Contractor;

use App\Domain\Models\Contractor\Contractor;
use App\Domain\Models\Contractor\ContractorId;
use App\Domain\Models\Contractor\CustomerId;
use App\Domain\Models\Contractor\SupplierId;
use Ramsey\Uuid\Uuid;

trait ContractorFactoryMethods
{
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

    private function aContractor(): Contractor
    {
        $contractor = Contractor::register(
            contractorId: $this->aContractorId(),
        );

        $contractor->releaseEvents();

        return $contractor;
    }
}
