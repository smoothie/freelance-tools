<?php

declare(strict_types=1);

namespace App\Domain\Model;

class Expression
{
    public function __construct(
        private string $fieldName,
        private string $operator,
        private string|int|array|null $value,
    ) {
    }

    public function fieldName(): string
    {
        return $this->fieldName;
    }

    public function operator(): string
    {
        return $this->operator;
    }

    public function value(): array|int|string|null
    {
        return $this->value;
    }

    public function isEmpty(): bool
    {
        return $this instanceof EmptyExpression;
    }
}
