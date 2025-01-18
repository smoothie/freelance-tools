<?php

declare(strict_types=1);

namespace App\Domain\Models\Common;

interface EventRecordingCapabilities
{
    public function releaseEvents(): array;
}
