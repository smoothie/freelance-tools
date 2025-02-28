<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Unit\Domain;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use Smoothie\FreelanceTools\Domain\Model\FilterCriteria;
use Smoothie\FreelanceTools\Domain\Model\PerformancePeriod;
use Smoothie\FreelanceTools\Tests\BasicTestCase;

#[Small]
#[Group('domain')]
#[Group('performance-period')]
#[CoversClass(PerformancePeriod::class)]
#[UsesClass(FilterCriteria::class)]
class PerformancePeriodTest extends BasicTestCase
{
    #[DataProvider('provideGoodCase')]
    public function testThatWeCanQueryForPerformancePeriods(string $performancePeriod, array $expectation): void
    {
        $actual = PerformancePeriod::fromString($performancePeriod);

        self::assertInstanceOf(PerformancePeriod::class, $actual);
        self::assertSame($expectation['startsOn'], $actual->performancePeriodStartsOn());
        self::assertSame($expectation['endsOn'], $actual->performancePeriodEndsOn());
        self::assertSame($expectation['query'], $actual->filterCriteria()->getQuery());
        self::assertSame($performancePeriod, $actual->performancePeriodId());
    }

    #[DataProvider('provideUnsupportedPeriods')]
    public function testThatWeWarnWhenWeReceiveAnUnknownPerformancePeriod(string $performancePeriod): void
    {
        $this->expectException(\LogicException::class);
        PerformancePeriod::fromString($performancePeriod);
    }

    public static function provideUnsupportedPeriods(): \Generator
    {
        yield PerformancePeriod::PERIOD_MONTH_LAST => ['foo'];
    }

    public static function provideGoodCase(): \Generator
    {
        yield PerformancePeriod::PERIOD_MONTH_LAST => [
            PerformancePeriod::PERIOD_MONTH_LAST,
            'expectation' => [
                'startsOn' => PerformancePeriod::FIRST_DAY_OF_LAST_MONTH,
                'endsOn' => PerformancePeriod::LAST_DAY_OF_LAST_MONTH,
                'query' => '(startDate >= :FIRST_DAY_OF_LAST_MONTH) AND (endDate <= :LAST_DAY_OF_LAST_MONTH)',
            ],
        ];
        yield PerformancePeriod::PERIOD_MONTH_LAST.'_no_matter' => [
            'laST_Month',
            'expectation' => [
                'startsOn' => PerformancePeriod::FIRST_DAY_OF_LAST_MONTH,
                'endsOn' => PerformancePeriod::LAST_DAY_OF_LAST_MONTH,
                'query' => '(startDate >= :FIRST_DAY_OF_LAST_MONTH) AND (endDate <= :LAST_DAY_OF_LAST_MONTH)',
            ],
        ];
        yield PerformancePeriod::PERIOD_MONTH_CURRENT => [
            PerformancePeriod::PERIOD_MONTH_CURRENT,
            'expectation' => [
                'startsOn' => PerformancePeriod::FIRST_DAY_OF_THE_MONTH,
                'endsOn' => PerformancePeriod::LAST_DAY_OF_THE_MONTH,
                'query' => '(startDate >= :FIRST_DAY_OF_THE_MONTH) AND (endDate <= :LAST_DAY_OF_THE_MONTH)',
            ],
        ];
    }
}
