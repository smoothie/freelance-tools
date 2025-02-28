<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Unit\Domain\Group;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use Smoothie\FreelanceTools\Domain\Model\ClientId;
use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;
use Smoothie\FreelanceTools\Domain\Model\Common\Duration;
use Smoothie\FreelanceTools\Domain\Model\Group\ListOfTasksInAProject;
use Smoothie\FreelanceTools\Domain\Model\Group\ListOfTasksInDays;
use Smoothie\FreelanceTools\Domain\Model\Group\TaskInAList;
use Smoothie\FreelanceTools\Domain\Model\ProjectId;
use Smoothie\FreelanceTools\Domain\Model\Task;
use Smoothie\FreelanceTools\Domain\Model\Timing;
use Smoothie\FreelanceTools\Tests\BasicTestCase;

#[Small]
#[Group('group')]
#[CoversClass(ListOfTasksInAProject::class)]
#[UsesClass(ClientId::class)]
#[UsesClass(DateTime::class)]
#[UsesClass(Duration::class)]
#[UsesClass(ListOfTasksInDays::class)]
#[UsesClass(ProjectId::class)]
#[UsesClass(Task::class)]
#[UsesClass(TaskInAList::class)]
#[UsesClass(Timing::class)]
class ListOfTasksInAProjectTest extends BasicTestCase
{
    use GroupFactoryMethods;

    public function testThatWeCanCreateAListOfTasksForAProject(): void
    {
        $actual = new ListOfTasksInAProject($this->aProject(), $this->aListType());
        self::assertSame($this->aListType(), $actual->listType());
        self::assertSame($this->aProject()->asString(), $actual->projectId()->asString());
        self::assertEmpty($actual->listsOfTasksInDays());
        self::assertEmpty($actual->totalDuration()->asInt());

        $actual->addTask($this->aTask());

        $day = '2025-02-14';
        $list = (new ListOfTasksInDays($day));
        $list->addTaskToList(
            new TaskInAList($day, $this->aDescription(), Duration::fromSeconds(10)),
        );
        self::assertEqualsCanonicalizing([$list], $actual->listsOfTasksInDays());
        self::assertSame(10, $actual->totalDuration()->asInt());

        $actual->addTask($this->aTask());

        $list->addTaskToList(new TaskInAList($day, $this->aDescription(), Duration::fromSeconds(10)));
        self::assertEqualsCanonicalizing([$list], $actual->listsOfTasksInDays());
        self::assertSame(20, $actual->totalDuration()->asInt());

        $actual->addTask($this->anotherTask(5));

        $day = '2025-02-15';
        $anotherList = (new ListOfTasksInDays($day));
        $anotherList->addTaskToList(new TaskInAList($day, $this->anotherDescription(), Duration::fromSeconds(50)));
        self::assertEqualsCanonicalizing([$list, $anotherList], $actual->listsOfTasksInDays());
        self::assertSame(70, $actual->totalDuration()->asInt());
    }
}
