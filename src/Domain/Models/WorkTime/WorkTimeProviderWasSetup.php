<?php

declare(strict_types=1);

namespace App\Domain\Models\WorkTime;

class WorkTimeProviderWasSetup
{
    public function __construct(
        private WorkTimeProviderId $workTimeProviderId,
        private WorkTimeProviderType $workTimeProviderType,
    ) {
    }

    public function workTimeProviderId(): WorkTimeProviderId
    {
        return $this->workTimeProviderId;
    }

    public function workTimeProviderType(): WorkTimeProviderType
    {
        return $this->workTimeProviderType;
    }
}
