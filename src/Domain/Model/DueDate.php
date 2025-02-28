<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\Model\Common\DateTime;

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
