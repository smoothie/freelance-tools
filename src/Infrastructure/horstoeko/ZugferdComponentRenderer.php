<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Infrastructure\horstoeko;

use Smoothie\FreelanceTools\Application\FilesystemInterface;
use Smoothie\FreelanceTools\Domain\Model\Component;
use Smoothie\FreelanceTools\Domain\Model\Invoice;
use Smoothie\FreelanceTools\Domain\Service\ComponentRenderer;

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
