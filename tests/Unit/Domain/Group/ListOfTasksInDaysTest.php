<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Group;

use App\Domain\Model\Common\Duration;
use App\Domain\Model\Group\ListOfTasksInDays;
use App\Domain\Model\Group\TaskInAList;
use App\Tests\BasicTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

#[Small]
#[Group('group')]
#[CoversClass(ListOfTasksInDays::class)]
#[CoversClass(Duration::class)]
#[CoversClass(TaskInAList::class)]
class ListOfTasksInDaysTest extends BasicTestCase
{
    use GroupFactoryMethods;

    public function testThatWeCanAddTasksToTheList(): void
    {
        $actual = new ListOfTasksInDays($this->aGroup());

        self::assertSame($this->aGroup(), $actual->group());
        self::assertEmpty($actual->duration()->asInt());
        self::assertEmpty($actual->tasks());

        $actual->addTaskToList($this->aGroupedTask(20));

        self::assertSame(20, $actual->duration()->asInt());
        self::assertEqualsCanonicalizing([$this->aGroupedTask(20)], $actual->tasks());

        $actual->addTaskToList($this->aGroupedTask(13));

        self::assertSame(33, $actual->duration()->asInt());
        self::assertEqualsCanonicalizing([$this->aGroupedTask(33)], $actual->tasks());

        $actual->addTaskToList($this->anotherGroupedTask(13));

        self::assertSame(46, $actual->duration()->asInt());
        self::assertEqualsCanonicalizing([$this->aGroupedTask(33), $this->anotherGroupedTask(13)], $actual->tasks());
    }
}
