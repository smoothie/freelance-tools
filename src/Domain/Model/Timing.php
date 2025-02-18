<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\Model\Common\DateTime;
use App\Domain\Model\Common\Duration;

class Timing
{
    public function __construct(
        private string $startTime,
        private string $endTime,
    ) {
    }

    public function startTime(): DateTime
    {
        return DateTime::fromString($this->startTime);
    }

    public function endTime(): DateTime
    {
        return DateTime::fromString($this->endTime);
    }

    public function duration(): Duration
    {
        return Duration::between($this->startTime(), $this->endTime());
    }
}
