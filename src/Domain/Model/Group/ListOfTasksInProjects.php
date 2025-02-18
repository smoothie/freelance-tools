<?php

declare(strict_types=1);

namespace App\Domain\Model\Group;

use App\Domain\Model\Task;
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

    //    public function toArray(): array
    //    {
    //       $result = [];
    //        foreach ($this->lists as $project => $list) {
    //            $result[$project] = [
    //                'totalDuration' => $list->totalDuration()->asInt(),
    //                $this->listType->value => [],
    //            ];
    //
    //            foreach ($list->listsOfTasksInDays() as $listOfTasksInDays) {
    //                $result[$project][$this->listType->value][$listOfTasksInDays->group()] = [
    //                    'duration' => $listOfTasksInDays->duration()->asInt(),
    //                    'tasks' => [],
    //                ];
    //
    //                foreach ($listOfTasksInDays->tasks() as $task) {
    //                    $result[$project][$this->listType->value][$listOfTasksInDays->group()]['tasks'][] = [
    //                        'description' => $task->description(),
    //                        'duration' => $task->duration()->asInt(),
    //                    ];
    //                }
    //            }
    //        }
    //
    //        return $result;
    //    }
}
