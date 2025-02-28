<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Unit\Domain;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use Smoothie\FreelanceTools\Domain\Model\EmptyExpression;
use Smoothie\FreelanceTools\Domain\Model\Expression;
use Smoothie\FreelanceTools\Domain\Model\FilterExpressions;
use Smoothie\FreelanceTools\Tests\BasicTestCase;

#[Small]
#[Group('domain')]
#[Group('filter')]
#[Group('expression')]
#[CoversClass(FilterExpressions::class)]
#[CoversClass(Expression::class)]
#[CoversClass(EmptyExpression::class)]
class FilterExpressionsTest extends BasicTestCase
{
    #[DataProvider('provideGoodCase')]
    public function testThatWeCanQueryForPerformancePeriods(array $assertions, array $expectation): void
    {
        $expressions = array_map(
            static fn (array $expression): Expression => new Expression(...$expression),
            $assertions['expressions'],
        );

        $actual = new FilterExpressions($expressions);

        self::assertTrue($expectation['startDate'] === null ? $actual->startDate()->isEmpty() : $actual->startDate()->isEmpty() === false);
        self::assertSame($expectation['startDate'], $actual->startDate()->value());
        self::assertSame($expectation['endDate'], $actual->endDate()->value());
        self::assertSame($expectation['duration'], $actual->duration()->value());
        self::assertSame($expectation['client'], $actual->client()->value());
        self::assertSame($expectation['project'], $actual->project()->value());
        self::assertSame($expectation['tag'], $actual->tag()->value());
        self::assertSame($expectation['description'], $actual->description()->value());
        self::assertSame($expressions, $actual->toArray());
    }

    public function testThatWeWarnWhenWeReceiveAnUnexpectedExpression(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new FilterExpressions(['woot']);
    }

    public static function provideGoodCase(): \Generator
    {
        $startDate = '___startDate___';
        $endDate = '___endDate___';
        $duration = '___duration___';
        $client = '___client___';
        $project = '___project___';
        $tag = '___tag___';
        $description = '___description___';
        $operator = 'LIKE';

        yield 'START_DATE' => [
            'assertions' => [
                'expressions' => [
                    ['fieldName' => 'startDate', 'operator' => $operator, 'value' => $startDate],
                ],
            ],
            'expectation' => [
                'startDate' => $startDate,
                'endDate' => null,
                'duration' => null,
                'client' => null,
                'project' => null,
                'tag' => null,
                'description' => null,
            ],
        ];
        yield 'END_DATE' => [
            'assertions' => [
                'expressions' => [
                    ['fieldName' => 'endDate', 'operator' => $operator, 'value' => $endDate],
                ],
            ],
            'expectation' => [
                'startDate' => null,
                'endDate' => $endDate,
                'duration' => null,
                'client' => null,
                'project' => null,
                'tag' => null,
                'description' => null,
            ],
        ];
        yield 'DURATION' => [
            'assertions' => [
                'expressions' => [
                    ['fieldName' => 'duration', 'operator' => $operator, 'value' => $duration],
                ],
            ],
            'expectation' => [
                'startDate' => null,
                'endDate' => null,
                'duration' => $duration,
                'client' => null,
                'project' => null,
                'tag' => null,
                'description' => null,
            ],
        ];
        yield 'CLIENT' => [
            'assertions' => [
                'expressions' => [
                    ['fieldName' => 'client', 'operator' => $operator, 'value' => $client],
                ],
            ],
            'expectation' => [
                'startDate' => null,
                'endDate' => null,
                'duration' => null,
                'client' => $client,
                'project' => null,
                'tag' => null,
                'description' => null,
            ],
        ];
        yield 'PROJECT' => [
            'assertions' => [
                'expressions' => [
                    ['fieldName' => 'project', 'operator' => $operator, 'value' => $project],
                ],
            ],
            'expectation' => [
                'startDate' => null,
                'endDate' => null,
                'duration' => null,
                'client' => null,
                'project' => $project,
                'tag' => null,
                'description' => null,
            ],
        ];
        yield 'TAG' => [
            'assertions' => [
                'expressions' => [
                    ['fieldName' => 'tag', 'operator' => $operator, 'value' => $tag],
                ],
            ],
            'expectation' => [
                'startDate' => null,
                'endDate' => null,
                'duration' => null,
                'client' => null,
                'project' => null,
                'tag' => $tag,
                'description' => null,
            ],
        ];
        yield 'DESCRIPTION' => [
            'assertions' => [
                'expressions' => [
                    ['fieldName' => 'description', 'operator' => $operator, 'value' => $description],
                ],
            ],
            'expectation' => [
                'operator' => $operator,
                'startDate' => null,
                'endDate' => null,
                'duration' => null,
                'client' => null,
                'project' => null,
                'tag' => null,
                'description' => $description,
            ],
        ];
    }
}
