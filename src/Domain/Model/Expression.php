<?php

declare(strict_types=1);

namespace App\Domain\Model;

class Expression
{
    /**
     * @param string|string[]|null $value
     */
    public function __construct(
        private string $fieldName,
        private string $operator,
        private string|array|null $value,
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

    /**
     * @return string|string[]|null
     */
    public function value(): array|string|null
    {
        return $this->value;
    }

    public function isEmpty(): bool
    {
        return $this instanceof EmptyExpression;
    }
}
