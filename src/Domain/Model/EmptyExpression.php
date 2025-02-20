<?php

declare(strict_types=1);

namespace App\Domain\Model;

class EmptyExpression extends Expression
{
    public function __construct()
    {
    }

    public function fieldName(): string
    {
        return '';
    }

    public function operator(): string
    {
        return '';
    }

    public function value(): string|array|null
    {
        return null;
    }
}
