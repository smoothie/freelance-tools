<?php

declare(strict_types=1);

namespace App\Domain\Model;

class ContactInformation
{
    public function __construct(
        private string $phone,
        private string $mail,
        private string $web,
    ) {
    }

    public function phone(): string
    {
        return $this->phone;
    }

    public function mail(): string
    {
        return $this->mail;
    }

    public function web(): string
    {
        return $this->web;
    }
}
