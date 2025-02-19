<?php

declare(strict_types=1);

namespace App\Infrastructure\Twig;

use App\Domain\Model\Common\DateTime;
use App\Domain\Model\Common\Duration;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TwigExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('sec_to_hour', [$this, 'secondToHumanReadableHour']),
            new TwigFilter('sec_to_hour_and_minute', [$this, 'secondToHumanReadableHourAndMinute']),
            new TwigFilter('to_date', [$this, 'toDate']),
        ];
    }

    public function toDate(string|DateTime|\DateTimeInterface $input): string
    {
        $date = $input;
        if (\is_string($input)) {
            try {
                $date = DateTime::fromDateString($input);
            } catch (\Throwable $exception) {
                $date = DateTime::fromString($input);
            }
        }
        if ($input instanceof \DateTimeInterface) {
            $date = DateTime::fromDateTime($input);
        }

        return $date->asPhpDateTime()->format('d.m.Y');
    }

    public function secondToHumanReadableHour(int|Duration $input): string
    {
        $seconds = $input;
        if ($input instanceof Duration) {
            $seconds = $input->asInt();
        }

        $hours = floor($seconds / 3600);
        $minutes = floor($seconds / 60) % 60;

        if ($minutes >= 30) {
            ++$hours;
        }

        return \sprintf('%1d', $hours);
    }

    public function secondToHumanReadableHourAndMinute(int|Duration $input): string
    {
        $seconds = $input;
        if ($input instanceof Duration) {
            $seconds = $input->asInt();
        }

        $hours = floor($seconds / 3600);
        $minutes = floor($seconds / 60) % 60;

        return \sprintf('%1d:%02d', $hours, $minutes);
    }
}
