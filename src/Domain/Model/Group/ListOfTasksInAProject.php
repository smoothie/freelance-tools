<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Domain\Model\Group;

use Smoothie\FreelanceTools\Domain\Model\Common\Duration;
use Smoothie\FreelanceTools\Domain\Model\ProjectId;
use Smoothie\FreelanceTools\Domain\Model\Task;

class ListOfTasksInAProject
{
    private Duration $totalDuration;

    /**
     * @var ListOfTasksInDays[]
     */
    private array $listOfTasksInDays = [];

    public function __construct(
        private ProjectId $projectId,
        private ListType $listType,
    ) {
        $this->totalDuration = Duration::fromSeconds(0);
    }

    public function projectId(): ProjectId
    {
        return $this->projectId;
    }

    public function totalDuration(): Duration
    {
        return $this->totalDuration;
    }

    public function listType(): ListType
    {
        return $this->listType;
    }

    /**
     * @return ListOfTasksInDays[]
     */
    public function listsOfTasksInDays(): array
    {
        return $this->listOfTasksInDays;
    }

    public function addTask(Task $task): void
    {
        switch ($this->listType) {
            case ListType::DAYS:
                $this->addTaskForDays($task);

                break;
            default:
                throw new \LogicException(\sprintf('Unsupported list type "%s"', $this->listType->value));
        }
    }

    private function addTaskForDays(Task $task): void
    {
        $cloned = clone $this;
        $description = $task->description();

        foreach ($task->timings() as $timing) {
            $day = $timing->startTime()->asDateString();
            $newTask = new TaskInAList($day, $description, $timing->duration());
            $cloned->totalDuration->add($timing->duration());

            foreach ($cloned->listOfTasksInDays as $listOfGroupedTasks) {
                if ($day === $listOfGroupedTasks->group()) {
                    $listOfGroupedTasks->addTaskToList($newTask);
                    $this->listOfTasksInDays = $cloned->listOfTasksInDays;
                    $this->totalDuration = $cloned->totalDuration;

                    continue 2;
                }
            }

            $newList = new ListOfTasksInDays($day);
            $newList->addTaskToList($newTask);

            $cloned->listOfTasksInDays[] = $newList;
            $this->totalDuration = $cloned->totalDuration;
            $this->listOfTasksInDays = $cloned->listOfTasksInDays;
        }
    }
}
