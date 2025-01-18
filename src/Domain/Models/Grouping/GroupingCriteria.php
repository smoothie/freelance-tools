<?php

declare(strict_types=1);

namespace App\Domain\Models\Grouping;

class GroupingCriteria
{
    private function __construct(private string $expression)
    {
    }

    public static function fromString(string $expression): self
    {
        return new self($expression);
    }

    public function getExpression(): string
    {
        return $this->expression;
    }
}
