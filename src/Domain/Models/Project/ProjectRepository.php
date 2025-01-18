<?php

declare(strict_types=1);

namespace App\Domain\Models\Project;

interface ProjectRepository
{
    public function getById(ProjectId $id): Project;
}
