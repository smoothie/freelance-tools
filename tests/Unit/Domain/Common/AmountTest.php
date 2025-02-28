<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Unit\Domain\Common;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Smoothie\FreelanceTools\Domain\Model\Common\Amount;
use Smoothie\FreelanceTools\Tests\BasicTestCase;

#[Small]
#[Group('domain')]
#[Group('common')]
#[Group('amount')]
#[CoversClass(Amount::class)]
class AmountTest extends BasicTestCase
{
    public function testThatWeCanCreateFromAFloat(): void
    {
        $input = 1500.12;
        $actual = Amount::fromFloat($input);

        $expectation = [
            'float' => 1500.12,
            'human' => '1.500,12',
        ];

        self::assertSame($expectation['float'], $actual->asFloat());
        self::assertSame($expectation['human'], $actual->asHumanReadable('de_DE'));
    }

    public function testThatWeCanMultiply(): void
    {
        $a = 1.1;
        $b = Amount::fromFloat(2.2);

        $actual = Amount::fromFloat($a)->multiply($b);

        $expectation = [
            'float' => 2.42,
            'human' => '2,42',
        ];

        self::assertSame($expectation['float'], $actual->asFloat());
        self::assertSame($expectation['human'], $actual->asHumanReadable('de_DE'));
    }

    public function testThatWeCanMultiplyFloats(): void
    {
        $a = 1.1;
        $b = 2.2;

        $actual = Amount::fromFloat($a)->multiply($b);

        $expectation = [
            'float' => 2.42,
            'human' => '2,42',
        ];

        self::assertSame($expectation['float'], $actual->asFloat());
        self::assertSame($expectation['human'], $actual->asHumanReadable('de_DE'));
    }

    public function testThatWeCanMultiplyIntegers(): void
    {
        $a = 1.1;
        $b = 2;

        $actual = Amount::fromFloat($a)->multiply($b);

        $expectation = [
            'float' => 2.2,
            'human' => '2,20',
        ];

        self::assertSame($expectation['float'], $actual->asFloat());
        self::assertSame($expectation['human'], $actual->asHumanReadable('de_DE'));
    }
}
