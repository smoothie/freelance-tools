<?php

declare(strict_types=1);

namespace App\Domain\Models\Contractor;

use App\Domain\Models\Project\Project;
use App\Domain\Models\Project\ProjectId;

interface ContractorRepository
{
    public function getById(ProjectId $id): Project;
    public function save(Project $project): void;
}
