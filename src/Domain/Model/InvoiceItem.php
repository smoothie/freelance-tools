<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Domain\Model;

use Brick\Math\BigDecimal;
use Smoothie\FreelanceTools\Domain\Model\Common\Amount;

class InvoiceItem
{
    private string $quantity;
    private ?int $durationInSeconds = null;

    public function __construct(
        private int $position,
        int|float|string $quantity,
        private string $description,
        private Amount $pricePerItem,
        private string $unit = 'HUR',
    ) {
        $decimalQuantity = BigDecimal::of($quantity)->stripTrailingZeros();

        if (! $decimalQuantity->isPositive()) {
            throw new \InvalidArgumentException('Quantity must be positive.');
        }

        if ($pricePerItem->isNegative()) {
            throw new \InvalidArgumentException('Price per item must not be negative.');
        }

        if (trim($description) === '') {
            throw new \InvalidArgumentException('Description must not be blank.');
        }

        if ($position < 1) {
            throw new \InvalidArgumentException('Position must be positive.');
        }

        if (! \in_array($unit, ['HUR', 'C62'], true)) {
            throw new \InvalidArgumentException('Unit must be HUR or C62.');
        }

        if ($decimalQuantity->getScale() > 6 || $decimalQuantity->isGreaterThan('999999.999999')) {
            throw new \InvalidArgumentException('Quantity must not exceed 999999.999999 or six decimal places.');
        }

        if (! $pricePerItem->isEqualTo($pricePerItem->rounded(6)) || $pricePerItem->asFloat() >= 1000000) {
            throw new \InvalidArgumentException('Unit price must be below 1000000 EUR with at most six decimal places.');
        }

        $this->quantity = (string) $decimalQuantity;
    }

    public static function fromTrackedSeconds(int $position, int $seconds, string $description, Amount $pricePerHour): self
    {
        if ($seconds < 1 || $seconds > 3599999999) {
            throw new \InvalidArgumentException('Tracked seconds must represent a positive duration below 1000000 hours.');
        }
        $item = new self($position, 1, $description, $pricePerHour);
        $item->quantity = (string) BigDecimal::of($seconds)->dividedBy(3600, 12, \Brick\Math\RoundingMode::HALF_UP)->stripTrailingZeros();
        $item->durationInSeconds = $seconds;

        return $item;
    }

    public function withPosition(int $position): self
    {
        if ($position < 1) {
            throw new \InvalidArgumentException('Position must be positive.');
        }
        $item = clone $this;
        $item->position = $position;

        return $item;
    }

    public function durationInSeconds(): ?int
    {
        return $this->durationInSeconds;
    }

    public function position(): int
    {
        return $this->position;
    }

    public function quantity(): int|float
    {
        $quantity = BigDecimal::of($this->quantity);

        if ($quantity->getScale() === 0 && $quantity->isLessThanOrEqualTo(\PHP_INT_MAX)) {
            return $quantity->toInt();
        }

        return $quantity->toFloat();
    }

    public function quantityAsDecimal(): string
    {
        return $this->quantity;
    }

    public function unit(): string
    {
        return $this->unit;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function pricePerItem(): Amount
    {
        return $this->pricePerItem;
    }

    public function priceTotal(): Amount
    {
        if ($this->durationInSeconds !== null) {
            return $this->pricePerItem->multiply(Amount::fromString((string) $this->durationInSeconds))->dividedBy(3600);
        }

        return $this->pricePerItem->multiply(Amount::fromString($this->quantity))->rounded();
    }
}
