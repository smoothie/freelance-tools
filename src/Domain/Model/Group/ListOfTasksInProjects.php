<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Domain\Model\Group;

use Smoothie\FreelanceTools\Domain\Model\Task;
use Webmozart\Assert\Assert;

class ListOfTasksInProjects
{
    /**
     * @var ListOfTasksInAProject[]
     */
    private array $lists = [];

    private function __construct(private ListType $listType)
    {
    }

    /**
     * @param list<mixed> $tasks
     */
    public static function fromTasks(array $tasks, ?ListType $listType = null): self
    {
        Assert::allIsInstanceOf($tasks, Task::class);
        if ($listType === null) {
            $listType = ListType::DAYS;
        }

        $self = new self($listType);

        foreach ($tasks as $task) {
            $self->addTask($task);
        }

        return $self;
    }

    public function listType(): ListType
    {
        return $this->listType;
    }

    /**
     * @return ListOfTasksInAProject[]
     */
    public function lists(): array
    {
        return $this->lists;
    }

    public function addTask(Task $task): void
    {
        $cloned = clone $this;

        $newProject = $task->projectId()->asString();

        if (\array_key_exists($newProject, $this->lists)) {
            $cloned->lists[$newProject]->addTask($task);
            $this->lists = $cloned->lists;

            return;
        }

        $newList = new ListOfTasksInAProject($task->projectId(), $this->listType);
        $newList->addTask($task);
        $cloned->lists[$newProject] = $newList;

        $this->lists = $cloned->lists;
    }
}
