<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Domain\Model\Common;

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

    public static function between(DateTime $start, DateTime $end): self
    {
        $diff = (int) abs($end->asPhpDateTime()->getTimestamp() - $start->asPhpDateTime()->getTimestamp());

        return new self($diff);
    }

    public function asInt(): int
    {
        return $this->seconds;
    }

    public function asHours(): int
    {
        $seconds = $this->seconds;
        $hours = floor($seconds / 3600);
        $minutes = floor($seconds / 60) % 60;

        if ($minutes >= 30) {
            ++$hours;
        }

        return (int) $hours;
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
