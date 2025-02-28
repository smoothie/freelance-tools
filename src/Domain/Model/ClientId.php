<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Domain\Model;

class ClientId implements \Stringable
{
    public function __construct(private string $id)
    {
    }

    public static function fromString(string $string): self
    {
        return new self($string);
    }

    public function asString(): string
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->asString();
    }
}
