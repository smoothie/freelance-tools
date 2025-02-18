<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Application\Clock;
use App\Domain\Model\Common\DateTime;

class SystemClock implements Clock
{
    private ?\DateTimeImmutable $now = null;

    public function currentTime(): \DateTimeImmutable
    {
        if ($this->now === null) {
            $this->now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        }

        return $this->now;
    }

    public function setCurrentDate(string $dateTime): void
    {
        $this->now = new \DateTimeImmutable($dateTime, new \DateTimeZone('UTC'));
    }

    public function lastDayOfLastMonth(): DateTime
    {
        return DateTime::fromString(
            $this->currentTime()
                ->modify('last day of last month')
                ->format(DateTime::DATE_TIME_FORMAT),
        );
    }

    public function firstDayOfLastMonth(): DateTime
    {
        return DateTime::fromString(
            $this->currentTime()
                ->modify('first day of last month')
                ->format(DateTime::DATE_TIME_FORMAT),
        );
    }

    public function lastDayOfTheMonth(): DateTime
    {
        return DateTime::fromString(
            $this->currentTime()
                ->modify('last day of this month')
                ->format(DateTime::DATE_TIME_FORMAT),
        );
    }

    public function firstDayOfTheMonth(): DateTime
    {
        return DateTime::fromString(
            $this->currentTime()
                ->modify('first day of this month')
                ->format(DateTime::DATE_TIME_FORMAT),
        );
    }
}
