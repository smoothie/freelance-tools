<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\Model\Common\Duration;

class Task
{
    public function __construct(
        private string $projectId,
        private string $clientId,
        private string $description,
        /**
         * @var string[]
         */
        private array $tags,
        /**
         * @var Timing[]
         */
        private array $timings,
    ) {
    }

    /**
     * @return string
     */
    public function projectId(): ProjectId
    {
        return ProjectId::fromString($this->projectId);
    }

    public function clientId(): ClientId
    {
        return ClientId::fromString($this->clientId);
    }

    public function description(): string
    {
        return $this->description;
    }

    /**
     * @return string[]
     */
    public function tags(): array
    {
        return $this->tags;
    }

    public function duration(): Duration
    {
        $duration = Duration::fromSeconds(0);

        foreach ($this->timings as $timing) {
            $duration->add($timing->duration());
        }

        return $duration;
    }

    /**
     * @return Timing[]
     */
    public function timings(): array
    {
        return $this->timings;
    }
}
