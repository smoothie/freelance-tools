<?php

declare(strict_types=1);

namespace App\Domain\Models\Timing;

use App\Domain\Models\Common\Duration;

class Timing
{
    private TimingId $timingId;
    private string $startDateTime;
    private string $endDateTime;
    private Duration $duration;
}
