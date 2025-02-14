<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Models\Common\DateTime;
use App\Domain\Models\Common\Duration;
use App\Domain\Models\Timing\TimingId;

class TrackTiming
{
    private function __construct(
        private string $timingId,
        private string $startDateTime,
        private string $endDateTime,
        private int $duration,
    ) {
    }

    public function timingId(): TimingId
    {
        return TimingId::fromString($this->timingId);
    }

    public function startDateTime(): DateTime
    {
        return DateTime::fromString($this->startDateTime);
    }

    public function endDateTime(): DateTime
    {
        return DateTime::fromString($this->endDateTime);
    }

    public function duration(): Duration
    {
        return Duration::fromSeconds($this->duration);
    }
}
