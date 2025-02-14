<?php

declare(strict_types=1);

namespace App\Domain\Models\Timesheet\Report;

use App\Domain\Models\Common\DateTime;
use App\Domain\Models\Common\Duration;

class TotalDuration
{
    private function __construct(private Duration $duration)
    {
    }

    public static function fromSeconds(int $duration): self
    {
        return new self(Duration::fromSeconds($duration));
    }

    public function toSeconds(): int
    {
        return $this->duration->asInt();
    }
}
