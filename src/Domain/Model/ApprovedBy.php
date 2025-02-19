<?php

declare(strict_types=1);

namespace App\Domain\Model;

class ApprovedBy
{
    public function __construct(
        private string $name,
        private string $company,
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function company(): string
    {
        return $this->company;
    }
}
