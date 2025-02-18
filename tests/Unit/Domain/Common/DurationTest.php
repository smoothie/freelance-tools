<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Common;

use App\Domain\Model\Common\DateTime;
use App\Domain\Model\Common\Duration;
use App\Tests\BasicTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

#[Small]
#[Group('common')]
#[CoversClass(Duration::class)]
#[CoversClass(DateTime::class)]
class DurationTest extends BasicTestCase
{
    public function testThatWeCanAddSeconds(): void
    {
        $actual = Duration::fromSeconds(0);
        $assertion = Duration::fromSeconds(10);
        $actual->add($assertion);

        self::assertSame(10, $actual->asInt());

        $actual->add($assertion);

        self::assertSame(20, $actual->asInt());
    }

    public function testThatWeCanSubtractSeconds(): void
    {
        $actual = Duration::fromSeconds(10);
        $assertion = Duration::fromSeconds(5);
        $actual->subtract($assertion);

        self::assertSame(5, $actual->asInt());
    }

    public function testThatWeCanGetADifferenceBetween(): void
    {
        $aTime = DateTime::fromString('2024-09-01 12:00:00');
        $anotherTime = DateTime::fromString('2024-09-01 12:05:17');

        self::assertSame(317, Duration::between($aTime, $anotherTime)->asInt());
        self::assertSame(317, Duration::between($anotherTime, $aTime)->asInt());
    }
}
