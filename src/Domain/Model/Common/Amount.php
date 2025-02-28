<?php

declare(strict_types=1);

namespace App\Domain\Model\Common;

class Amount
{
    public const int PRECISION = 5;

    private function __construct(private float $value)
    {
    }

    public static function fromFloat(float $value): self
    {
        return new self($value);
    }

    public function asFloat(): float
    {
        return $this->value;
    }

    public function asHumanReadable(string $locale): string
    {
        $formatter = \NumberFormatter::create($locale, \NumberFormatter::DEFAULT_STYLE);
        $formatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 2);

        return $formatter->format($this->asFloat());
    }

    public function multiply(float|self $amount): self
    {
        if (\is_float($amount)) {
            $amount = self::fromFloat($amount);
        }

        $result = round($this->asFloat() * $amount->asFloat(), self::PRECISION);

        return new self($result);
    }

    public function add(float|self $amount): self
    {
        if (\is_float($amount)) {
            $amount = self::fromFloat($amount);
        }

        $result = round($this->asFloat() + $amount->asFloat(), self::PRECISION);

        return new self($result);
    }
}
