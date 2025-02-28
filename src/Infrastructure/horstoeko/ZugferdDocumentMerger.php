<?php

declare(strict_types=1);

namespace App\Infrastructure\horstoeko;

use App\Application\PdfAndXmlMerger;
use horstoeko\zugferd\ZugferdDocumentPdfMerger;

class ZugferdDocumentMerger implements PdfAndXmlMerger
{
    public function merge(string $pdf, string $xml): string
    {
        $merger = new ZugferdDocumentPdfMerger($xml, $pdf);
        $merger->generateDocument();

        return $merger->downloadString();
    }
}
