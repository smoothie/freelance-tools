<?php

declare(strict_types=1);

namespace App\Tests\Integration\Doctrine\Lexer;

use App\Domain\Model\Common\DateTime;
use App\Domain\Model\Expression;
use App\Domain\Model\FilterCriteria;
use App\Domain\Model\FilterExpressions;
use App\Domain\Model\PerformancePeriod;
use App\Infrastructure\Doctrine\Lexer\FilterCriteriaQueryParser;
use App\Infrastructure\Doctrine\Lexer\Lexer;
use App\Infrastructure\SystemClock;
use App\Tests\BasicTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Large;

#[Large]
#[Group('integration')]
#[Group('integration-doctrine')]
#[CoversClass(FilterCriteriaQueryParser::class)]
#[CoversClass(Expression::class)]
#[CoversClass(FilterExpressions::class)]
class FilterCriteriaExtractorTest extends BasicTestCase
{
    #[DataProvider('providePerformancePeriodFilterCriteria')]
    public function testThatWeCanQuery(array $assertions, array $expectations): void
    {
        $clock = new SystemClock();
        $clock->setCurrentDate(DateTime::fromDateString($assertions['currentDate']));

        $extractor = new FilterCriteriaQueryParser(new Lexer(), $clock);
        $actual = $extractor->parse(FilterCriteria::fromString($assertions['query']));

        self::assertEqualsCanonicalizing($expectations, $actual->toArray());
    }

    public static function providePerformancePeriodFilterCriteria(): \Generator
    {
        yield 'A_PROJECT' => [
            'assertions' => [
                'query' => '(project = \'PIM\')',
                'currentDate' => '2025-01-16',
            ],
            'expectations' => [
                new Expression('project', '=', 'PIM'),
            ],
        ];

        yield 'A_PROJECT_IN_LIST' => [
            'assertions' => [
                'query' => '(project IN (\'PIM\', \'FOO\'))',
                'currentDate' => '2025-01-16',
            ],
            'expectations' => [
                new Expression('project', 'IN', ['PIM', 'FOO']),
            ],
        ];

        yield 'ONLY_PERFORMANCE_PERIOD_LAST_MONTH' => [
            'assertions' => [
                'query' => PerformancePeriod::fromString('LAST_MONTH')->filterCriteria()->getQuery(),
                'currentDate' => '2025-01-16',
            ],
            'expectations' => [
                new Expression('startDate', '>=', '2024-12-01'),
                new Expression('endDate', '<=', '2024-12-31'),
            ],
        ];
        yield 'ONLY_PERFORMANCE_PERIOD_CURRENT_MONTH' => [
            'assertions' => [
                'query' => PerformancePeriod::fromString('CURRENT_MONTH')->filterCriteria()->getQuery(),
                'currentDate' => '2025-02-16',
            ],
            'expectations' => [
                new Expression('startDate', '>=', '2025-02-01'),
                new Expression('endDate', '<=', '2025-02-28'),
            ],
        ];
        yield 'FOR_A_CLIENT_AND_IN_PROJECTS_AND_A_TIME_PERIOD' => [
            'assertions' => [
                'query' => \sprintf(
                    '%s AND %s AND %s',
                    PerformancePeriod::fromString('LAST_MONTH')->filterCriteria()->getQuery(),
                    '(project IN (\'PIM\', \'FOO\'))',
                    '(client = \'ME\')',
                ),
                'currentDate' => '2025-01-16',
            ],
            'expectations' => [
                new Expression('startDate', '>=', '2024-12-01'),
                new Expression('endDate', '<=', '2024-12-31'),
                new Expression('project', 'IN', ['PIM', 'FOO']),
                new Expression('client', '=', 'ME'),
            ],
        ];
    }
}
