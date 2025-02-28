<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Application;

use Smoothie\FreelanceTools\Domain\Model\Invoice;
use Smoothie\FreelanceTools\Domain\Model\InvoiceId;

class GeneratedAnInvoice
{
    public function __construct(
        private Invoice $invoice,
        private string $exportFormat,
        private string $rendered,
    ) {
    }

    public function invoiceId(): InvoiceId
    {
        return $this->invoice->invoiceId();
    }

    public function invoice(): Invoice
    {
        return $this->invoice;
    }

    public function rendered(): string
    {
        return $this->rendered;
    }

    public function exportFormat(): string
    {
        return $this->exportFormat;
    }
}
