<?php

declare(strict_types=1);

namespace App\Domain\Models\Contractor;

class ContractorWasRegistered
{
    public function __construct(
        private ContractorId $contractorId,
    ) {
    }

    public function contractorId(): ContractorId
    {
        return $this->contractorId;
    }
}
