<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Project;

use App\Domain\Models\Project\Project;
use App\Domain\Models\Project\ProjectWasAssigned;
use App\Domain\Models\Project\RecordedTaskForProject;
use App\Tests\BasicTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;

#[Small]
#[Group('domain')]
#[Group('model')]
#[Group('project')]
#[CoversClass(Project::class)]
class ProjectTest extends BasicTestCase
{
    use ProjectFactoryMethods;

    public function testWeCanAssignAProject(): void
    {
        $aProjectId = $this->aProjectId();
        $aContractorId = $this->aContractorId();
        $aCustomerId = $this->aCustomerId();
        $aSupplierId = $this->aSupplierId();

        $project = Project::assign(
            projectId: $aProjectId,
            contractorId: $aContractorId,
            customerId: $aCustomerId,
            supplierId: $aSupplierId,
        );

        self::assertArrayContainsObjectOfType(ProjectWasAssigned::class, $project->releaseEvents());

        self::assertSame($aProjectId, $project->projectId());
        self::assertSame($aContractorId, $project->contractorId());
        self::assertSame($aCustomerId, $project->customerId());
        self::assertSame($aSupplierId, $project->supplierId());
    }

    public function testATaskWasRecordedForAProject(): void
    {
        $project = $this->aProject();

        $taskId = $this->aTaskId();
        $project->recordTask(taskId: $taskId);

        self::assertArrayContainsObjectOfType(RecordedTaskForProject::class, $project->releaseEvents());
    }
}
