<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Unit\Domain;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use Smoothie\FreelanceTools\Domain\Model\BilledBy;
use Smoothie\FreelanceTools\Domain\Model\BilledTo;
use Smoothie\FreelanceTools\Domain\Model\Common\Amount;
use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;
use Smoothie\FreelanceTools\Domain\Model\ContactInformation;
use Smoothie\FreelanceTools\Domain\Model\DueDate;
use Smoothie\FreelanceTools\Domain\Model\Invoice;
use Smoothie\FreelanceTools\Domain\Model\InvoiceId;
use Smoothie\FreelanceTools\Domain\Model\InvoiceItem;
use Smoothie\FreelanceTools\Domain\Model\PaymentInformation;
use Smoothie\FreelanceTools\Domain\Model\ProjectId;
use Smoothie\FreelanceTools\Tests\BasicTestCase;

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
