<?php

declare(strict_types=1);

namespace App\Domain\Model\Common;

interface EventRecordingCapabilities
{
    public function releaseEvents(): array;
}
