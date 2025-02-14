<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Models\Common\DateTime;

interface Clock
{
    public function currentTime(): DateTime;
}
