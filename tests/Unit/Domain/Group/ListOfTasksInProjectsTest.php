<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Group;

use App\Domain\Model\ClientId;
use App\Domain\Model\Common\DateTime;
use App\Domain\Model\Common\Duration;
use App\Domain\Model\Group\ListOfTasksInAProject;
use App\Domain\Model\Group\ListOfTasksInDays;
use App\Domain\Model\Group\ListOfTasksInProjects;
use App\Domain\Model\Group\ListType;
use App\Domain\Model\Group\TaskInAList;
use App\Domain\Model\ProjectId;
use App\Domain\Model\Task;
use App\Domain\Model\Timing;
use App\Tests\BasicTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;

#[Small]
#[Group('group')]
#[CoversClass(ListOfTasksInProjects::class)]
#[UsesClass(ClientId::class)]
#[UsesClass(DateTime::class)]
#[UsesClass(Duration::class)]
#[UsesClass(ListOfTasksInAProject::class)]
#[UsesClass(ListOfTasksInDays::class)]
#[UsesClass(ProjectId::class)]
#[UsesClass(Task::class)]
#[UsesClass(TaskInAList::class)]
#[UsesClass(Timing::class)]
class ListOfTasksInProjectsTest extends BasicTestCase
{
    use GroupFactoryMethods;

    public function testThatWeCanGroupTasksForProjects(): void
    {
        $actual = ListOfTasksInProjects::fromTasks([$this->aTask()]);

        $expectation = [];

        $aInAProjectList = new ListOfTasksInAProject($this->aProject(), $this->aListType());
        $aInAProjectList->addTask($this->aTask());

        $expectation[] = $aInAProjectList;

        self::assertCount(1, $actual->lists());
        self::assertSame(ListType::DAYS, $actual->listType());
        self::assertEqualsCanonicalizing(
            $expectation,
            $actual->lists(),
        );

        $actual->addTask($this->aTask());
        $aInAProjectList->addTask($this->aTask());

        self::assertEqualsCanonicalizing(
            $expectation,
            $actual->lists(),
        );

        $actual->addTask($this->anotherTask(5));
        $aInAProjectList->addTask($this->anotherTask(5));
        self::assertEqualsCanonicalizing(
            $expectation,
            $actual->lists(),
        );
    }
}
