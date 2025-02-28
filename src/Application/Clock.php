<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Application;

use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;

interface Clock
{
    public function currentTime(): \DateTimeImmutable;

    public function setCurrentDate(DateTime $dateTime): void;

    public function lastDayOfLastMonth(): DateTime;

    public function firstDayOfLastMonth(): DateTime;

    public function lastDayOfTheMonth(): DateTime;

    public function firstDayOfTheMonth(): DateTime;
}
