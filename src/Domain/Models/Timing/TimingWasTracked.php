<?php

declare(strict_types=1);

namespace App\Domain\Models\Timing;

use App\Domain\Models\Common\DateTime;
use App\Domain\Models\Common\Duration;

class TimingWasTracked
{
    public function __construct(
        private TimingId $timingId,
        private DateTime $startDateTime,
        private DateTime $endDateTime,
        private Duration $duration,
    ) {
    }

    public function timingId(): TimingId
    {
        return $this->timingId;
    }

    public function startDateTime(): DateTime
    {
        return $this->startDateTime;
    }

    public function endDateTime(): DateTime
    {
        return $this->endDateTime;
    }

    public function duration(): Duration
    {
        return $this->duration;
    }
}
