<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Domain\Model;

class PaymentInformation
{
    public function __construct(
        private string $bank,
        private string $iban,
        private string $bic,
    ) {
    }

    public function bank(): string
    {
        return $this->bank;
    }

    public function iban(): string
    {
        return $this->iban;
    }

    public function bic(): string
    {
        return $this->bic;
    }
}
