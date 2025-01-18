<?php

declare(strict_types=1);

namespace App\Domain\Models\Timesheet;

class ExportFormat
{
    public function __construct(private string $format)
    {
    }

    public function getFormat(): string
    {
        return $this->format;
    }
}
