<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Unit\Domain;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;
use Smoothie\FreelanceTools\Domain\Model\Common\Duration;
use Smoothie\FreelanceTools\Domain\Model\Timing;
use Smoothie\FreelanceTools\Tests\BasicTestCase;

#[Small]
#[Group('domain')]
#[Group('timing')]
#[CoversClass(Timing::class)]
#[UsesClass(DateTime::class)]
#[UsesClass(Duration::class)]
class TimingTest extends BasicTestCase
{
    public function testThatATask(): void
    {
        $aThing = [
            'startTime' => '2024-01-02 12:00:00', 'endTime' => '2024-01-02 12:00:05',
        ];

        $expectation = [
            'startTime' => '2024-01-02 12:00:00',
            'endTime' => '2024-01-02 12:00:05',
            'duration' => 5,
        ];

        $actual = new Timing(
            startTime: $aThing['startTime'],
            endTime: $aThing['endTime'],
        );

        self::assertSame($expectation['startTime'], $actual->startTime()->asString());
        self::assertSame($expectation['endTime'], $actual->endTime()->asString());
        self::assertSame($expectation['duration'], $actual->duration()->asInt());
    }
}
