<?php

declare(strict_types=1);

namespace App\Domain\Models\Timing;


interface TimingRepository
{
    public function getById(TimingId $timingId): Timing;
    public function save(Timing $timing): void;
}
