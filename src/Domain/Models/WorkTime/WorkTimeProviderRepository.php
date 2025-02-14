<?php

declare(strict_types=1);

namespace App\Domain\Models\WorkTime;

interface WorkTimeProviderRepository
{
    public function getById(WorkTimeProviderId $workTimeProviderId): WorkTimeProvider;
    public function save(WorkTimeProvider $workTimeProvider): void;
}
