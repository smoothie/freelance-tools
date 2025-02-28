<?php

declare(strict_types=1);

namespace App\Application;

interface PdfAndXmlMerger
{
    public function merge(string $pdf, string $xml): string;
}
