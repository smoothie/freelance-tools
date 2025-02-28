<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Infrastructure;

use Smoothie\FreelanceTools\Application\Clock;
use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;

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

    public function setCurrentDate(DateTime $dateTime): void
    {
        $this->now = $dateTime->asPhpDateTime();
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
