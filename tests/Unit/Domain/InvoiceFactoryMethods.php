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

trait InvoiceFactoryMethods
{
    protected function anInvoiceTitle(): string
    {
        return 'Invoice Number 202502-02';
    }

    protected function aProjectId(): ProjectId
    {
        return ProjectId::fromString('cheesecake-agile');
    }

    protected function anInvoiceId(): InvoiceId
    {
        return InvoiceId::fromString('Invoice Number 202502-02');
    }

    protected function aDescription(): string
    {
        return 'description';
    }

    protected function aTaxRate(): Amount
    {
        return Amount::fromFloat(19.0);
    }

    protected function aBillingDate(): DateTime
    {
        return DateTime::fromDateString('2025-02-03');
    }

    protected function aDueDate(): DueDate
    {
        return new DueDate(
            dueDate: DateTime::fromDateString('2025-03-03'),
            termOfPaymentInDays: 30,
        );
    }

    protected function aDeliveredAt(): DateTime
    {
        return DateTime::fromDateString('2025-01-31');
    }

    protected function aPageNumber(): int
    {
        return 1;
    }

    protected function aBilledBy(): BilledBy
    {
        return new BilledBy(
            name: 'Marc Eichenseher',
            street: 'Anotherstreet 4',
            location: '55555 Woot',
            vatId: 'DE000000001',
            country: 'DE',
            contactInformation: new ContactInformation(
                phone: '+4900012345678',
                mail: 'some@example.com',
                web: 'marceichenseher.de',
            ),
            paymentInformation: new PaymentInformation(
                bank: 'A Bank',
                iban: 'DE11 1111 0000 1111 1111 1111',
                bic: 'AABICYA1111',
            ),
        );
    }

    protected function aBilledTo(): BilledTo
    {
        return new BilledTo(
            name: 'BilledTo GmbH',
            street: 'Somestreet 1',
            location: '55555 Some',
            vatId: 'DE900000009',
            country: 'DE',
        );
    }

    protected function anInvoiceItem(): InvoiceItem
    {
        return new InvoiceItem(
            position: 1,
            quantity: 10,
            description: 'Bake and chill cheesecakes',
            pricePerItem: Amount::fromFloat(70.0),
        );
    }

    protected function anInvoice(): Invoice
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

        return $invoice;
    }
}
