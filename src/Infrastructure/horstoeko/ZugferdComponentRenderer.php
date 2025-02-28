<?php

declare(strict_types=1);

namespace App\Infrastructure\horstoeko;

use App\Application\FilesystemInterface;
use App\Domain\Model\Component;
use App\Domain\Model\Invoice;
use App\Domain\Service\ComponentRenderer;

class ZugferdComponentRenderer implements ComponentRenderer
{
    public function __construct(
        private EInvoiceBuilder $eInvoiceBuilder,
        private FilesystemInterface $filesystem,
    ) {
    }

    public function render(Component $component): string
    {
        if (! $component instanceof Invoice) {
            throw new \LogicException('Only invoices can be rendered as a E-Invoice.');
        }

        $xml = $this->eInvoiceBuilder->build($component);

        $this->filesystem->dumpExport($component->fileName('xml'), $xml);

        return $xml;
    }
}
