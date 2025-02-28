<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain;

use App\Domain\Model\BilledBy;
use App\Domain\Model\BilledTo;
use App\Domain\Model\Common\Amount;
use App\Domain\Model\Common\DateTime;
use App\Domain\Model\ContactInformation;
use App\Domain\Model\DueDate;
use App\Domain\Model\Invoice;
use App\Domain\Model\InvoiceId;
use App\Domain\Model\InvoiceItem;
use App\Domain\Model\PaymentInformation;
use App\Domain\Model\ProjectId;
use App\Tests\BasicTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;

#[Small]
#[Group('domain')]
#[Group('invoice')]
#[CoversClass(Invoice::class)]
#[CoversClass(InvoiceId::class)]
#[UsesClass(InvoiceItem::class)]
#[UsesClass(BilledBy::class)]
#[UsesClass(BilledTo::class)]
#[UsesClass(Amount::class)]
#[UsesClass(DateTime::class)]
#[UsesClass(ContactInformation::class)]
#[UsesClass(DueDate::class)]
#[UsesClass(PaymentInformation::class)]
#[UsesClass(ProjectId::class)]
class InvoiceTest extends BasicTestCase
{
    use InvoiceFactoryMethods;

    public function testThatAnInvoice(): void
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
        );

        $invoice->addInvoiceItem($this->anInvoiceItem());

        $netAmount = Amount::fromFloat(700.0);
        $vatAmount = Amount::fromFloat(133.0);
        $grossAmount = Amount::fromFloat(833.0);

        self::assertSame($this->anInvoiceTitle(), $invoice->title());
        self::assertSame('2025-02-02 - BilledTo GmbH - Invoice Number 202502-02 - Marc Eichenseher', $invoice->fileName(now: DateTime::fromDateString('2025-02-02')));
        self::assertSame('pdf/timesheet/invoice.html.twig', $invoice->template());
        self::assertSame($this->aDescription(), $invoice->description());
        self::assertEqualsCanonicalizing($netAmount, $invoice->netAmount());
        self::assertEqualsCanonicalizing($vatAmount, $invoice->vatAmount());
        self::assertEqualsCanonicalizing($grossAmount, $invoice->grossAmount());
        self::assertEqualsCanonicalizing($this->aProjectId(), $invoice->projectId());
        self::assertEqualsCanonicalizing($this->anInvoiceId(), $invoice->invoiceId());
        self::assertEqualsCanonicalizing($this->aTaxRate(), $invoice->taxRate());
        self::assertEqualsCanonicalizing($this->aBillingDate(), $invoice->billingDate());
        self::assertEqualsCanonicalizing($this->aDueDate(), $invoice->dueDate());
        self::assertEqualsCanonicalizing($this->aBilledBy(), $invoice->billedBy());
        self::assertEqualsCanonicalizing($this->aBilledTo(), $invoice->billedTo());
        self::assertEqualsCanonicalizing($this->aDeliveredAt(), $invoice->deliveredAt());

        $context = [
            'title' => $this->anInvoiceTitle(),
            'project' => $this->aProjectId(),
            'netAmount' => $netAmount,
            'vatAmount' => $vatAmount,
            'grossAmount' => $grossAmount,
            'invoiceItems' => [$this->anInvoiceItem()],
            'invoiceId' => $this->anInvoiceId(),
            'description' => $this->aDescription(),
            'taxRate' => $this->aTaxRate(),
            'billingDate' => $this->aBillingDate(),
            'dueDate' => $this->aDueDate(),
            'sender' => $this->aBilledBy(),
            'recipient' => $this->aBilledTo(),
            'deliveredAt' => $this->aDeliveredAt(),
            'lastPageNumber' => $this->aPageNumber(),
        ];

        self::assertEqualsCanonicalizing($context, $invoice->context());
    }
}
