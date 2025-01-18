<?php

declare(strict_types=1);

namespace App\Domain\Models\Common;

use Webmozart\Assert\Assert;

final class Duration
{
    private int $seconds;

    private function __construct(int $seconds)
    {
        Assert::greaterThanEq($seconds, 0);

        $this->seconds = $seconds;
    }

    public static function fromSeconds(int $seconds): self
    {
        return new self($seconds);
    }

    public function asInt(): int
    {
        return $this->seconds;
    }

    public function add(self $duration): void
    {
        $this->seconds += $duration->asInt();
    }

    public function subtract(self $duration): void
    {
        $this->seconds -= $duration->asInt();
    }
}
