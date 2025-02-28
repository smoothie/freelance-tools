<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Unit\Domain;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use Smoothie\FreelanceTools\Domain\Model\ClientId;
use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;
use Smoothie\FreelanceTools\Domain\Model\Common\Duration;
use Smoothie\FreelanceTools\Domain\Model\ProjectId;
use Smoothie\FreelanceTools\Domain\Model\Task;
use Smoothie\FreelanceTools\Domain\Model\Timing;
use Smoothie\FreelanceTools\Tests\BasicTestCase;

#[Small]
#[Group('domain')]
#[Group('task')]
#[CoversClass(Task::class)]
#[UsesClass(Timing::class)]
#[UsesClass(ClientId::class)]
#[UsesClass(DateTime::class)]
#[UsesClass(Duration::class)]
#[UsesClass(ProjectId::class)]
class TaskTest extends BasicTestCase
{
    public function testThatATask(): void
    {
        $aThing = [
            'projectId' => 'someProjectId',
            'clientId' => 'someClientId',
            'description' => 'some_description',
            'tags' => ['a_tag'],
            'timings' => [
                ['startTime' => '2024-01-02 12:00:00', 'endTime' => '2024-01-02 12:00:05'],
                ['startTime' => '2024-01-03 12:00:00', 'endTime' => '2024-01-03 12:00:05'],
            ],
        ];

        $timings = array_map(static fn (array $timing): Timing => new Timing(...$timing), $aThing['timings']);
        $duration = 10;

        $actual = new Task(
            projectId: $aThing['projectId'],
            clientId: $aThing['clientId'],
            description: $aThing['description'],
            tags: $aThing['tags'],
            timings: $timings,
        );

        self::assertSame($aThing['projectId'], $actual->projectId()->asString());
        self::assertSame($aThing['clientId'], $actual->clientId()->asString());
        self::assertSame($aThing['description'], $actual->description());
        self::assertSame($aThing['tags'], $actual->tags());
        self::assertSame($duration, $actual->duration()->asInt());
        self::assertSame($timings, $actual->timings());
    }
}
