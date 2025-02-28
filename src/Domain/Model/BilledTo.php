<?php

declare(strict_types=1);

namespace App\Domain\Model;

class BilledTo
{
    public function __construct(
        private string $name,
        private string $street,
        private string $location,
        private string $vatId,
        private string $country,
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function street(): string
    {
        return $this->street;
    }

    public function location(): string
    {
        return $this->location;
    }

    public function vatId(): string
    {
        return $this->vatId;
    }

    public function country(): string
    {
        return $this->country;
    }
}
