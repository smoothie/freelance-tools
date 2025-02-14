<?php

declare(strict_types=1);

namespace App\Domain\Models\Timing;

use App\Domain\Models\Common\DateTime;
use App\Domain\Models\Common\Duration;
use App\Domain\Models\Common\EventRecordingCapabilities;
use App\Domain\Models\Common\UsesEventRecordingCapabilities;
use JetBrains\PhpStorm\Deprecated;

class Timing implements EventRecordingCapabilities
{
    use UsesEventRecordingCapabilities;

    private TimingId $timingId;
    private DateTime $startDateTime;
    private DateTime $endDateTime;
    private Duration $duration;

    private function __construct()
    {
    }

    public static function trackTime(
        TimingId $timingId,
        DateTime $startDateTime,
        DateTime $endDateTime,
        #[Deprecated('consider to remove the duration and make it computed')]
        Duration $duration,
    ): self {
        $self = new self();
        $self->timingId = $timingId;
        $self->startDateTime = $startDateTime;
        $self->endDateTime = $endDateTime;
        $self->duration = $duration;

        $self->events[] = new TimingWasTracked(
            timingId: $timingId,
            startDateTime: $startDateTime,
            endDateTime: $endDateTime,
            duration: $duration,
        );

        return $self;
    }

    public function getTimingId(): TimingId
    {
        return $this->timingId;
    }

    public function getStartDateTime(): DateTime
    {
        return $this->startDateTime;
    }

    public function getEndDateTime(): DateTime
    {
        return $this->endDateTime;
    }

    public function getDuration(): Duration
    {
        return $this->duration;
    }
}
