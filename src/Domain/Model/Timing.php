<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Domain\Model;

use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;
use Smoothie\FreelanceTools\Domain\Model\Common\Duration;

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
