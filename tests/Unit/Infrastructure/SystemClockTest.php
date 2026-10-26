<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Unit\Infrastructure;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;
use Smoothie\FreelanceTools\Infrastructure\SystemClock;
use Smoothie\FreelanceTools\Tests\BasicTestCase;

#[Small]
#[Group('infrastructure')]
#[CoversClass(SystemClock::class)]
#[UsesClass(DateTime::class)]
class SystemClockTest extends BasicTestCase
{
    #[DataProvider('provideMonthBoundaries')]
    public function testMonthBoundaries(
        string $currentDate,
        string $firstDayOfLastBeforeMonth,
        string $firstDayOfLastMonth,
        string $lastDayOfLastMonth,
        string $firstDayOfTheMonth,
        string $lastDayOfTheMonth,
    ): void {
        $clock = new SystemClock();
        $clock->setCurrentDate(DateTime::fromDateString($currentDate));

        self::assertSame($firstDayOfLastBeforeMonth, $clock->firstDayOfLastBeforeMonth()->asDateString());
        self::assertSame($firstDayOfLastMonth, $clock->firstDayOfLastMonth()->asDateString());
        self::assertSame($lastDayOfLastMonth, $clock->lastDayOfLastMonth()->asDateString());
        self::assertSame($firstDayOfTheMonth, $clock->firstDayOfTheMonth()->asDateString());
        self::assertSame($lastDayOfTheMonth, $clock->lastDayOfTheMonth()->asDateString());
        self::assertSame($currentDate, $clock->currentTime()->format('Y-m-d'));
    }

    public static function provideMonthBoundaries(): \Generator
    {
        yield 'march after leap february' => ['2024-03-31', '2024-01-01', '2024-02-01', '2024-02-29', '2024-03-01', '2024-03-31'];
        yield 'leap day' => ['2024-02-29', '2023-12-01', '2024-01-01', '2024-01-31', '2024-02-01', '2024-02-29'];
        yield 'march after common february' => ['2025-03-31', '2025-01-01', '2025-02-01', '2025-02-28', '2025-03-01', '2025-03-31'];
        yield 'first day of year' => ['2025-01-01', '2024-11-01', '2024-12-01', '2024-12-31', '2025-01-01', '2025-01-31'];
        yield 'last day of year' => ['2024-12-31', '2024-10-01', '2024-11-01', '2024-11-30', '2024-12-01', '2024-12-31'];
    }
}
