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
use Smoothie\FreelanceTools\Domain\Model\Group\ListOfTasksInProjects;
use Smoothie\FreelanceTools\Domain\Model\Group\ListType;
use Smoothie\FreelanceTools\Domain\Model\Group\TaskInAList;
use Smoothie\FreelanceTools\Domain\Model\ProjectId;
use Smoothie\FreelanceTools\Domain\Model\Task;
use Smoothie\FreelanceTools\Domain\Model\Timing;
use Smoothie\FreelanceTools\Tests\BasicTestCase;

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
