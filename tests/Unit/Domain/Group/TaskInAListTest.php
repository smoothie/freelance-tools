<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Unit\Domain\Group;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use Smoothie\FreelanceTools\Domain\Model\Common\Duration;
use Smoothie\FreelanceTools\Domain\Model\Group\TaskInAList;
use Smoothie\FreelanceTools\Tests\BasicTestCase;

#[Small]
#[Group('group')]
#[CoversClass(TaskInAList::class)]
#[UsesClass(Duration::class)]
class TaskInAListTest extends BasicTestCase
{
    use GroupFactoryMethods;

    public function testThatWeCanMergeDurationsWhenADescriptionAndGroupBelongTogether(): void
    {
        $actual = new TaskInAList($this->aGroup(), $this->aDescription(), $this->aDuration());
        $anotherTask = new TaskInAList($this->aGroup(), $this->aDescription(), $this->aDuration(20));
        self::assertSame($this->aGroup(), $actual->group());
        self::assertSame($this->aDescription(), $actual->description());
        self::assertSame($this->aDuration()->asInt(), $actual->duration()->asInt());

        $actual->mergeDuration($anotherTask);
        self::assertSame(30, $actual->duration()->asInt());
    }

    public function testThatWeDoNotMergeDurationsWhenADescriptionAndGroupDoNotBelongTogether(): void
    {
        $actual = new TaskInAList($this->aGroup(), $this->aDescription(), $this->aDuration());
        $anotherTask = new TaskInAList($this->anotherGroup(), $this->aDescription(), $this->aDuration(20));
        $andAnotherTask = new TaskInAList($this->aGroup(), $this->anotherDescription(), $this->aDuration(20));

        $actual->mergeDuration($anotherTask);
        self::assertSame(10, $actual->duration()->asInt());

        $actual->mergeDuration($andAnotherTask);
        self::assertSame(10, $actual->duration()->asInt());
    }
}
