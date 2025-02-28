<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Application;

interface PdfAndXmlMerger
{
    public function merge(string $pdf, string $xml): string;
}
