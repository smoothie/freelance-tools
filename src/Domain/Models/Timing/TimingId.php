<?php

declare(strict_types=1);

namespace App\Domain\Models\Timing;

use App\Domain\Models\Common\UsesUuid;
use App\Domain\Models\Common\Uuid;

class TimingId implements Uuid
{
    use UsesUuid;

    public function equals(self $timingId): bool
    {
        return $this->id === $timingId->id;
    }
}
