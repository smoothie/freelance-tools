<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Domain\Model\Common;

use Webmozart\Assert\Assert;

trait UsesUuid
{
    private string $id;

    private function __construct(string $id)
    {
        Assert::uuid($id);

        $this->id = $id;
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
