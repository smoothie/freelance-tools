<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Unit\Domain\Group;

use Smoothie\FreelanceTools\Domain\Model\ClientId;
use Smoothie\FreelanceTools\Domain\Model\Common\Duration;
use Smoothie\FreelanceTools\Domain\Model\Group\ListType;
use Smoothie\FreelanceTools\Domain\Model\Group\TaskInAList;
use Smoothie\FreelanceTools\Domain\Model\ProjectId;
use Smoothie\FreelanceTools\Domain\Model\Task;
use Smoothie\FreelanceTools\Domain\Model\Timing;

trait GroupFactoryMethods
{
    protected function aTask(int $amountOfTimings = 1): Task
    {
        return new Task(
            projectId: $this->aProject()->asString(),
            clientId: $this->aClient()->asString(),
            description: $this->aDescription(),
            tags: $this->aTag(),
            timings: $this->someTimings($amountOfTimings),
        );
    }

    protected function anotherTask(int $amountOfTimings = 2): Task
    {
        return new Task(
            projectId: $this->aProject()->asString(),
            clientId: $this->aClient()->asString(),
            description: $this->anotherDescription(),
            tags: $this->aTag(),
            timings: $this->someTimings(
                $amountOfTimings,
                startTime: '2025-02-15 12:00:00',
                endTime: '2025-02-15 12:00:10',
            ),
        );
    }

    protected function aTag(): array
    {
        return ['A_TAG'];
    }

    protected function aListType(): ListType
    {
        return ListType::DAYS;
    }

    protected function someTimings(
        int $amountOfTimings = 1,
        string $startTime = '2025-02-14 12:00:00',
        string $endTime = '2025-02-14 12:00:10',
    ): array {
        $timings = [];
        for ($i = 0; $i < $amountOfTimings; ++$i) {
            $timings[] = new Timing($startTime, $endTime);
        }

        return $timings;
    }

    protected function aProject(): ProjectId
    {
        return ProjectId::fromString('aProject');
    }

    protected function aClient(): ClientId
    {
        return ClientId::fromString('aClient');
    }

    protected function aGroup(): string
    {
        return 'aGroup';
    }

    protected function anotherGroup(): string
    {
        return 'anotherGroup';
    }

    protected function aDescription(): string
    {
        return 'aDescription';
    }

    protected function anotherDescription(): string
    {
        return 'anotherDescription';
    }

    protected function aDuration(int $durationInSec = 10): Duration
    {
        return Duration::fromSeconds($durationInSec);
    }

    protected function aGroupedTask(int $durationInSec = 10): TaskInAList
    {
        return new TaskInAList($this->aGroup(), $this->aDescription(), $this->aDuration($durationInSec));
    }

    protected function anotherGroupedTask(int $durationInSec = 10): TaskInAList
    {
        return new TaskInAList($this->anotherGroup(), $this->aDescription(), $this->aDuration($durationInSec));
    }
}
