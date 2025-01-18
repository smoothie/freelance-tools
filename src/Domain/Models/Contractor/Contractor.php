<?php

declare(strict_types=1);

namespace App\Domain\Models\Contractor;

use App\Domain\Models\Common\EventRecordingCapabilities;
use App\Domain\Models\Common\UsesEventRecordingCapabilities;

class Contractor implements EventRecordingCapabilities
{
    use UsesEventRecordingCapabilities;

    private ContractorId $contractorId;

    public function __construct()
    {
    }

    public static function register(
        ContractorId $contractorId,
    ): self {
        $project = new self();
        $project->contractorId = $contractorId;

        $project->events[] = new ContractorWasRegistered(
            $contractorId,
        );

        return $project;
    }

    public function contractorId(): ContractorId
    {
        return $this->contractorId;
    }
}
