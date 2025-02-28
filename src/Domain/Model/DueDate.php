<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Domain\Model;

use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;

class DueDate
{
    public function __construct(
        private DateTime $dueDate,
        private int $termOfPaymentInDays,
    ) {
    }

    public function dueDate(): DateTime
    {
        return $this->dueDate;
    }

    public function termOfPaymentInDays(): int
    {
        return $this->termOfPaymentInDays;
    }
}
