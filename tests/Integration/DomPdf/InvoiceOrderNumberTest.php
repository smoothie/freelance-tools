<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Integration\DomPdf;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\UsesNamespace;
use Smoothie\FreelanceTools\Domain\Model\Invoice;
use Smoothie\FreelanceTools\Infrastructure\horstoeko\EInvoiceBuilder;
use Smoothie\FreelanceTools\Infrastructure\Twig\TwigExtension;
use Smoothie\FreelanceTools\Tests\Unit\Domain\InvoiceFactoryMethods;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

#[Large]
#[CoversClass(Invoice::class)]
#[CoversClass(EInvoiceBuilder::class)]
#[CoversClass(TwigExtension::class)]
#[UsesNamespace('Smoothie\FreelanceTools\Domain\Model')]
class InvoiceOrderNumberTest extends KernelTestCase
{
    use InvoiceFactoryMethods;

    public function testThatPdfTemplateIncludesTheSuppliedOrderNumber(): void
    {
        $invoice = $this->anInvoiceWithOrderNumber('PO-2025-0042');
        $html = self::getContainer()->get(Environment::class)->render($invoice->template(), $invoice->context());

        self::assertStringContainsString('<th>Bestellnummer</th>', $html);
        self::assertStringContainsString('<td>PO-2025-0042</td>', $html);
    }

    public function testThatPdfTemplateOmitsAMissingOrderNumber(): void
    {
        $invoice = $this->anInvoice();
        $html = self::getContainer()->get(Environment::class)->render($invoice->template(), $invoice->context());

        self::assertStringNotContainsString('Bestellnummer', $html);
    }

    public function testThatEInvoiceIncludesTheSuppliedOrderNumber(): void
    {
        $xml = (new EInvoiceBuilder())->build($this->anInvoiceWithOrderNumber('PO-2025-0042'));

        self::assertStringContainsString('<ram:BuyerOrderReferencedDocument>', $xml);
        self::assertStringContainsString('<ram:IssuerAssignedID>PO-2025-0042</ram:IssuerAssignedID>', $xml);
    }

    public function testThatEInvoiceOmitsAMissingOrderNumber(): void
    {
        $xml = (new EInvoiceBuilder())->build($this->anInvoice());

        self::assertStringNotContainsString('BuyerOrderReferencedDocument', $xml);
    }

    private function anInvoiceWithOrderNumber(string $orderNumber): Invoice
    {
        $invoice = new Invoice(
            title: $this->anInvoiceTitle(),
            projectId: $this->aProjectId(),
            invoiceId: $this->anInvoiceId(),
            description: $this->aDescription(),
            taxRate: $this->aTaxRate(),
            billingDate: $this->aBillingDate(),
            dueDate: $this->aDueDate(),
            billedBy: $this->aBilledBy(),
            billedTo: $this->aBilledTo(),
            deliveredAt: $this->aDeliveredAt(),
            lastPageNumber: $this->aPageNumber(),
            orderNumber: $orderNumber,
        );
        $invoice->addInvoiceItem($this->anInvoiceItem());

        return $invoice;
    }
}
