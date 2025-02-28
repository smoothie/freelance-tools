<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Domain\Model;

class BilledBy
{
    public function __construct(
        private string $name,
        private string $street,
        private string $location,
        private string $vatId,
        private string $country,
        private ContactInformation $contactInformation,
        private PaymentInformation $paymentInformation,
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function street(): string
    {
        return $this->street;
    }

    public function location(): string
    {
        return $this->location;
    }

    public function vatId(): string
    {
        return $this->vatId;
    }

    public function country(): string
    {
        return $this->country;
    }

    public function contactInformation(): ContactInformation
    {
        return $this->contactInformation;
    }

    public function paymentInformation(): PaymentInformation
    {
        return $this->paymentInformation;
    }
}
