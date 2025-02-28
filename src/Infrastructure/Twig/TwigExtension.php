<?php

declare(strict_types=1);

namespace App\Infrastructure\Twig;

use App\Domain\Model\Common\Amount;
use App\Domain\Model\Common\DateTime;
use App\Domain\Model\Common\Duration;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Intl\Countries;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TwigExtension extends AbstractExtension
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('sec_to_hour', [$this, 'secondToHumanReadableHour']),
            new TwigFilter('sec_to_hour_and_minute', [$this, 'secondToHumanReadableHourAndMinute']),
            new TwigFilter('to_date', [$this, 'toDate']),
            new TwigFilter('amount_to_human', [$this, 'amountToHumanReadable']),
            new TwigFilter('unit', [$this, 'unit']),
            new TwigFilter('date_to_performance_period', [$this, 'toPerformancePeriod']),
            new TwigFilter('to_e164_phone_number', [$this, 'toPhoneNumber']),
            new TwigFilter('to_pretty_phone_number', [$this, 'toPrettyPhoneNumber']),
            new TwigFilter('country', [$this, 'toCountry']),
        ];
    }

    public function toCountry(string $countryCode): string
    {
        return Countries::getName($countryCode);
    }

    public function toPhoneNumber(string $input, string $locale = 'de_DE'): string
    {
        return $this->toAPhoneNumber($input, false, $locale);
    }

    public function toPrettyPhoneNumber(string $input, string $locale = 'de_DE'): string
    {
        return $this->toAPhoneNumber($input, true, $locale);
    }

    private function toAPhoneNumber(string $input, bool $pretty, string $locale = 'de_DE'): string
    {
        $util = PhoneNumberUtil::getInstance();

        try {
            $phoneNumber = $util->parse($input, $locale);
            if ($pretty) {
                return $util->format($phoneNumber, PhoneNumberFormat::INTERNATIONAL);
            }

            return $util->format($phoneNumber, PhoneNumberFormat::E164);
        } catch (\Throwable $exception) {
            return $input;
        }
    }

    public function toPerformancePeriod(DateTime $date, string $locale = 'de_DE'): string
    {
        return \IntlDateFormatter::create(
            $locale,
            timeType: \IntlDateFormatter::NONE,
            pattern: 'MMMM Y',
        )
            ->format($date->asPhpDateTime());
    }

    public function unit(string $unit): string
    {
        return match ($unit) {
            'HUR' => $this->translator->trans('unit.'.$unit, domain: 'tools'),
            default => $unit,
        };
    }

    public function amountToHumanReadable(int|float|Amount $amount, string $locale = 'de_DE'): string
    {
        if (\is_int($amount)) {
            $amount = Amount::fromFloat((float) $amount);
        }
        if (\is_float($amount)) {
            $amount = Amount::fromFloat($amount);
        }

        return $amount->asHumanReadable($locale);
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
