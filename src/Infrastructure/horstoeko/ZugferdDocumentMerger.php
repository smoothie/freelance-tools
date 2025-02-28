<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Infrastructure\horstoeko;

use horstoeko\zugferd\ZugferdDocumentPdfMerger;
use Smoothie\FreelanceTools\Application\PdfAndXmlMerger;

class ZugferdDocumentMerger implements PdfAndXmlMerger
{
    public function merge(string $pdf, string $xml): string
    {
        $merger = new ZugferdDocumentPdfMerger($xml, $pdf);
        $merger->generateDocument();

        return $merger->downloadString();
    }
}
