<?php

declare(strict_types=1);

namespace App\Domain\Models\Filter;

class FilterCriteria
{
    private function __construct(private string $query)
    {
    }

    public static function fromString(string $query): self
    {
        return new self($query);
    }

    public function getQuery(): string
    {
        return $this->query;
    }
}
