<?php

declare(strict_types=1);

namespace App\Domain\Model;

class FilterCriteria
{
    private function __construct(private string $query)
    {
    }

    public static function fromString(string $query): self
    {
        return new self($query);
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function withExactProject(ProjectId $projectId): void
    {
        $this->query = \sprintf('%s AND (project = \'%s\')', $this->query, $projectId->asString());
    }
}
