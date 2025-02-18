<?php

declare(strict_types=1);

namespace App\Domain\Model\Common;

trait UsesEventRecordingCapabilities
{
    /**
     * @var object[]
     */
    private $events = [];

    protected function recordThat(object $event): void
    {
        $this->events[] = $event;
    }

    public function releaseEvents(): array
    {
        $events = $this->events;

        $this->events = [];

        return $events;
    }
}
