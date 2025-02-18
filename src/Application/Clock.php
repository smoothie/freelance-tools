<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Model\Common\DateTime;

interface Clock
{
    public function currentTime(): \DateTimeImmutable;

    public function setCurrentDate(string $dateTime): void;

    public function lastDayOfLastMonth(): DateTime;

    public function firstDayOfLastMonth(): DateTime;

    public function lastDayOfTheMonth(): DateTime;

    public function firstDayOfTheMonth(): DateTime;
}
