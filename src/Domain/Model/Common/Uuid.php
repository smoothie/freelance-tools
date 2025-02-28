<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Domain\Model\Common;

interface Uuid extends \Stringable
{
    public static function fromString(string $string): self;

    public function asString(): string;
}
