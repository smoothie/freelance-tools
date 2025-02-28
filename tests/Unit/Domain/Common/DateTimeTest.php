<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Unit\Domain\Common;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;
use Smoothie\FreelanceTools\Tests\BasicTestCase;

#[Small]
#[Group('domain')]
#[Group('common')]
#[CoversClass(DateTime::class)]
class DateTimeTest extends BasicTestCase
{
    public function testThatWeCanCreateFromAString(): void
    {
        $date = '2024-02-12 12:01:02';
        $actual = DateTime::fromString($date);

        self::assertSame($date, $actual->asString());
    }

    public function testThatWeCanCreateFromADate(): void
    {
        $date = '2024-02-12';
        $actual = DateTime::fromDateString($date);

        self::assertSame($date, $actual->asDateString());
    }

    public function testThatWeCanCreateFromPhpInstance(): void
    {
        $date = '2024-02-12 12:01:02';
        $actual = DateTime::fromDateTime(new \DateTimeImmutable($date));

        self::assertSame($date, $actual->asString());
    }

    public function testThatWeWarnWhenUseAnUnsupportedDateFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        DateTime::fromString('20.01.2025 12:01:02');
    }
}
