<?php

declare(strict_types=1);

namespace App\Domain\Models\Pattern;

class TitlePattern
{
    private function __construct(private string $pattern)
    {
    }

    public static function fromString(string $pattern): self
    {
        return new self($pattern);
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }
}
