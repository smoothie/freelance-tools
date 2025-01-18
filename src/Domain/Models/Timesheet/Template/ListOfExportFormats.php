<?php

declare(strict_types=1);

namespace App\Domain\Models\Timesheet\Template;

use App\Domain\Models\Timesheet\ExportFormat;
use Webmozart\Assert\Assert;

class ListOfExportFormats
{
    /**
     * @var array<ExportFormat>
     */
    private array $exportFormats;

    private function __construct()
    {
    }

    /**
     * @param array<ExportFormat> $exportFormats
     */
    public static function fromArray(array $exportFormats = []): self
    {
        Assert::allIsInstanceOf($exportFormats, ExportFormat::class);

        $list = new self();
        $list->exportFormats = $exportFormats;

        return $list;
    }

    public function exportFormats(): array
    {
        return $this->exportFormats;
    }
}
