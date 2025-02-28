<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Domain\Service;

use Smoothie\FreelanceTools\Domain\Model\FilterCriteria;
use Smoothie\FreelanceTools\Domain\Model\Task;

interface WorkTimeProcessor
{
    /**
     * @return Task[]
     */
    public function getListOfTasks(FilterCriteria $filter): array;
}
