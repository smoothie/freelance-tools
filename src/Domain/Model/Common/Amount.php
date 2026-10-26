<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Domain\Model\Common;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class Amount
{
    public const int PRECISION = 5;

    private function __construct(private BigDecimal $value)
    {
        $this->value = $value->stripTrailingZeros();
    }

    public static function fromFloat(float $value): self
    {
        return new self(BigDecimal::of($value));
    }

    public static function fromString(string $value): self
    {
        return new self(BigDecimal::of($value));
    }

    public function asFloat(): float
    {
        return $this->value->toFloat();
    }

    public function asHumanReadable(string $locale, int $maximumFractionDigits = 2): string
    {
        $formatter = \NumberFormatter::create($locale, \NumberFormatter::DEFAULT_STYLE);
        $formatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 2);
        $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $maximumFractionDigits);

        return $formatter->format($this->asFloat());
    }

    public function multiply(float|self $amount): self
    {
        if (\is_float($amount)) {
            $amount = self::fromFloat($amount);
        }

        return new self($this->value->multipliedBy($amount->value));
    }

    public function dividedBy(int $divisor, int $scale = 2): self
    {
        return new self($this->value->dividedBy($divisor, $scale, RoundingMode::HALF_UP));
    }

    public function add(float|self $amount): self
    {
        if (\is_float($amount)) {
            $amount = self::fromFloat($amount);
        }

        return new self($this->value->plus($amount->value));
    }

    public function subtract(float|self $amount): self
    {
        if (\is_float($amount)) {
            $amount = self::fromFloat($amount);
        }

        return new self($this->value->minus($amount->value));
    }

    public function isEqualTo(self $amount): bool
    {
        return $this->value->isEqualTo($amount->value);
    }

    public function isNegative(): bool
    {
        return $this->value->isNegative();
    }

    public function rounded(int $scale = 2): self
    {
        return new self($this->value->toScale($scale, RoundingMode::HALF_UP));
    }

    public function asDecimal(int $scale = 2): string
    {
        return (string) $this->value->toScale($scale, RoundingMode::HALF_UP);
    }
}
