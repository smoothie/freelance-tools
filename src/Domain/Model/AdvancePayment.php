<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Domain\Model;

use Smoothie\FreelanceTools\Domain\Model\Common\Amount;
use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;
use Webmozart\Assert\Assert;

final class AdvancePayment
{
    public function __construct(
        private string $invoiceNumber,
        private DateTime $invoiceDate,
        private DateTime $receivedAt,
        private Amount $netAmount,
        private Amount $vatAmount,
        private Amount $grossAmount,
    ) {
        Assert::notWhitespaceOnly($invoiceNumber);
        Assert::greaterThan($grossAmount->asFloat(), 0);
        Assert::greaterThanEq($netAmount->asFloat(), 0);
        Assert::greaterThanEq($vatAmount->asFloat(), 0);
        Assert::true($netAmount->add($vatAmount)->isEqualTo($grossAmount), 'Payment net and VAT must add up to gross.');
        Assert::greaterThanEq($receivedAt->asDateString(), $invoiceDate->asDateString());
        foreach ([$netAmount, $vatAmount, $grossAmount] as $amount) {
            Assert::true($amount->isEqualTo($amount->rounded()), 'Payment amounts must be expressed in cents.');
        }
    }

    public function invoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function invoiceDate(): DateTime
    {
        return $this->invoiceDate;
    }

    public function receivedAt(): DateTime
    {
        return $this->receivedAt;
    }

    public function netAmount(): Amount
    {
        return $this->netAmount;
    }

    public function vatAmount(): Amount
    {
        return $this->vatAmount;
    }

    public function grossAmount(): Amount
    {
        return $this->grossAmount;
    }
}
