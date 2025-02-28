<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Domain\Model\Group;

use Smoothie\FreelanceTools\Domain\Model\Common\Duration;

class TaskInAList
{
    public function __construct(
        private string $group,
        private string $description,
        private Duration $duration,
    ) {
    }

    public function group(): string
    {
        return $this->group;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function duration(): Duration
    {
        return $this->duration;
    }

    public function mergeDuration(self $groupedTask): void
    {
        $cloned = clone $this;
        if ($groupedTask->group() !== $cloned->group()
            || $groupedTask->description() !== $cloned->description()) {
            return;
        }

        $cloned->duration->add($groupedTask->duration());

        $this->duration = $cloned->duration;
    }
}
