<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Domain\Model;

class ProvidedBy
{
    public function __construct(
        private string $name,
        private string $street,
        private string $location,
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
}
