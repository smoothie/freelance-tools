<?php

declare(strict_types=1);

namespace App\Domain\Models\Timesheet\Report;

use App\Domain\Models\Common\DateTime;

class StartDate
{
    private function __construct(private DateTime $dateTime)
    {
    }

    public static function fromString(string $dateTime): self
    {
        return new self(DateTime::fromString($dateTime));
    }

    public function toString(): string
    {
        return $this->dateTime->asString();
    }

    public function toPhpDateTime(): \DateTimeImmutable
    {
        return $this->dateTime->asPhpDateTime();
    }
}
