<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Group;

use App\Domain\Model\ClientId;
use App\Domain\Model\Common\DateTime;
use App\Domain\Model\Common\Duration;
use App\Domain\Model\Group\ListOfTasksInAProject;
use App\Domain\Model\Group\ListOfTasksInDays;
use App\Domain\Model\Group\TaskInAList;
use App\Domain\Model\ProjectId;
use App\Domain\Model\Task;
use App\Domain\Model\Timing;
use App\Tests\BasicTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

#[Small]
#[Group('group')]
#[CoversClass(ClientId::class)]
#[CoversClass(DateTime::class)]
#[CoversClass(Duration::class)]
#[CoversClass(ListOfTasksInAProject::class)]
#[CoversClass(ListOfTasksInDays::class)]
#[CoversClass(ProjectId::class)]
#[CoversClass(Task::class)]
#[CoversClass(TaskInAList::class)]
#[CoversClass(Timing::class)]
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
