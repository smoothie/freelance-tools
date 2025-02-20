<?php

declare(strict_types=1);

namespace App\Domain\Model\Common;

use Webmozart\Assert\Assert;

class DateTime
{
    public const string DATE_TIME_FORMAT = 'Y-m-d H:i:s';
    public const string DATE_FORMAT = 'Y-m-d';

    private string $dateTime;

    private function __construct(string $dateTime, string $timeZone)
    {
        try {
            $dateTimeImmutable = \DateTimeImmutable::createFromFormat(
                self::DATE_TIME_FORMAT,
                $dateTime,
                new \DateTimeZone($timeZone),
            );
            if ($dateTimeImmutable === false) {
                throw new \InvalidArgumentException('The provided date/time string did not match the expected format');
            }
        } catch (\Throwable $throwable) {
            throw new \InvalidArgumentException(
                \sprintf(
                    'Invalid date/time format. Provided: %s, expected format: %s',
                    $dateTime,
                    self::DATE_TIME_FORMAT,
                ),
                0,
                $throwable,
            );
        }

        $this->dateTime = $dateTimeImmutable
            ->setTimezone(new \DateTimeZone('UTC'))
            ->format(self::DATE_TIME_FORMAT);
    }

    public static function fromString(string $dateTime, string $timeZone = 'UTC'): self
    {
        return new self($dateTime, $timeZone);
    }

    public static function fromDateString(string $date, string $timeZone = 'UTC'): self
    {
        return new self($date.' 00:00:00', $timeZone);
    }

    public static function fromDateTime(\DateTimeInterface $dateTime): self
    {
        Assert::methodExists($dateTime, 'setTimezone', 'The dateTime must support setting a timezone. Received a "%s"');
        \assert(method_exists($dateTime, 'setTimezone'));

        $dateTimeAsString = $dateTime
            ->setTimezone(new \DateTimeZone('UTC'))
            ->format(self::DATE_TIME_FORMAT);

        return new self($dateTimeAsString, 'UTC');
    }

    public function asString(): string
    {
        return $this->dateTime;
    }

    public function asDateString(): string
    {
        return $this->asPhpDateTime()->format(self::DATE_FORMAT);
    }

    public function asPhpDateTime(): \DateTimeImmutable
    {
        $dateTime = \DateTimeImmutable::createFromFormat(
            self::DATE_TIME_FORMAT,
            $this->dateTime,
            new \DateTimeZone('UTC'),
        );

        Assert::isInstanceOf($dateTime, \DateTimeImmutable::class);

        return $dateTime;
    }
}
