<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Domain\Model;

use Smoothie\FreelanceTools\Domain\Model\Common\Amount;

class InvoiceItem
{
    public function __construct(
        private int $position,
        private int $quantity,
        private string $description,
        private Amount $pricePerItem,
    ) {
    }

    public function position(): int
    {
        return $this->position;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function unit(): string
    {
        // At some point we probably want to provide packages. Then we'll come back to that one.
        return 'HUR';
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
        return $this->pricePerItem->multiply($this->quantity);
    }
}
