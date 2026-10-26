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
    public function testDecimalArithmeticIsExact(): void
    {
        $amount = Amount::fromString('9007199254740993.123456');

        self::assertSame('9007199254740993.123456', $amount->asDecimal(6));
        self::assertSame('9007199254740993.223456', $amount->add(Amount::fromString('0.1'))->asDecimal(6));
        self::assertSame('9007199254740993.023456', $amount->subtract(Amount::fromString('0.1'))->asDecimal(6));
        self::assertSame('0.000000000001', Amount::fromString('0.000001')->multiply(Amount::fromString('0.000001'))->asDecimal(12));
        self::assertSame('0.30', Amount::fromString('0.1')->add(0.2)->asDecimal());
        self::assertSame('-0.10', Amount::fromString('0.1')->subtract(0.2)->asDecimal());
        self::assertSame('9007199254740993.123456', $amount->asDecimal(6));
    }

    public function testDivisionRoundsOnlyAtTheRequestedScale(): void
    {
        self::assertSame('90.18', Amount::fromString('324630')->dividedBy(3600)->asDecimal());
        self::assertSame('0.333333', Amount::fromString('1')->dividedBy(3, 6)->asDecimal(6));
        self::assertTrue(Amount::fromString('1.000000000001')->isEqualTo(Amount::fromString('1.000000000001')));
        self::assertFalse(Amount::fromString('1.000000000001')->isEqualTo(Amount::fromString('1')));
        self::assertSame('1,234567', Amount::fromString('1.234567')->asHumanReadable('de_DE', 6));
        self::assertSame('1,23', Amount::fromString('1.234567')->asHumanReadable('de_DE'));
    }

    public function testRoundingUsesHalfUp(): void
    {
        self::assertSame('1.01', Amount::fromString('1.005')->asDecimal());
        self::assertSame('-1.01', Amount::fromString('-1.005')->asDecimal());
        self::assertSame('1.00', Amount::fromString('1.004999')->rounded()->asDecimal());
        self::assertSame('1.235', Amount::fromString('1.2345')->rounded(3)->asDecimal(3));
        self::assertSame('2', Amount::fromString('1.5')->asDecimal(0));
        self::assertSame('1.005', Amount::fromString('1.005')->asDecimal(3));
        self::assertSame('0.00', Amount::fromString('-0')->asDecimal());
    }

    public function testNegativeAmountsAreDetectedWithoutRounding(): void
    {
        self::assertTrue(Amount::fromString('-0.0000000001')->isNegative());
        self::assertFalse(Amount::fromString('0')->isNegative());
        self::assertFalse(Amount::fromString('1')->isNegative());
    }

    public function testNegativeScaleIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Amount::fromString('1')->rounded(-1);
    }

    public function testMalformedDecimalIsRejected(): void
    {
        $this->expectException(\Brick\Math\Exception\NumberFormatException::class);

        Amount::fromString('1,23');
    }

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
