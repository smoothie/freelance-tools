<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Model\BilledBy;
use App\Domain\Model\BilledTo;
use App\Domain\Model\Common\Amount;
use App\Domain\Model\Common\DateTime;
use App\Domain\Model\Common\Duration;
use App\Domain\Model\ContactInformation;
use App\Domain\Model\DueDate;
use App\Domain\Model\InvoiceId;
use App\Domain\Model\InvoiceItem;
use App\Domain\Model\PaymentInformation;
use App\Domain\Model\ProjectId;

class GenerateInvoice
{
    /**
     * @param array{name: string, street: string, location: string, vatId: string, country: string, phone: string, mail: string, web: string, bank: string, iban: string, bic: string} $billedBy
     * @param array{name: string, street: string, location: string, vatId: string, country: string } $billedTo
     */
    public function __construct(
        private string $invoiceId,
        private string $project,
        private string $description,
        private int $durationInSeconds,
        private string $deliveredAt,
        private float $taxRate,
        private string $billingDate,
        private float $pricePerHour,
        private int $termOfPaymentInDays,
        private array $billedBy,
        private array $billedTo,
        private string $exportFormat = 'PDF',
    ) {
    }

    public function invoiceId(): InvoiceId
    {
        return InvoiceId::fromString($this->invoiceId);
    }

    public function project(): ProjectId
    {
        return ProjectId::fromString($this->project);
    }

    public function exportFormat(): string
    {
        return $this->exportFormat;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function taxRate(): Amount
    {
        return Amount::fromFloat($this->taxRate);
    }

    public function billingDate(): DateTime
    {
        return DateTime::fromDateString($this->billingDate);
    }

    public function dueDate(): DueDate
    {
        return new DueDate(
            dueDate: DateTime::fromDateTime(
                $this->billingDate()
                    ->asPhpDateTime()
                    ->add(new \DateInterval('P'.$this->termOfPaymentInDays.'D')),
            ),
            termOfPaymentInDays: $this->termOfPaymentInDays,
        );
    }

    /**
     * @return InvoiceItem[]
     */
    public function invoiceItems(): array
    {
        return [
            new InvoiceItem(
                1,
                Duration::fromSeconds($this->durationInSeconds)->asHours(),
                $this->description,
                Amount::fromFloat($this->pricePerHour),
            ),
        ];
    }

    public function billedBy(): BilledBy
    {
        return new BilledBy(
            name: $this->billedBy['name'],
            street: $this->billedBy['street'],
            location: $this->billedBy['location'],
            vatId: $this->billedBy['vatId'],
            country: $this->billedBy['country'],
            contactInformation: new ContactInformation(
                phone: $this->billedBy['phone'],
                mail: $this->billedBy['mail'],
                web: $this->billedBy['web'],
            ),
            paymentInformation: new PaymentInformation(
                bank: $this->billedBy['bank'],
                iban: $this->billedBy['iban'],
                bic: $this->billedBy['bic'],
            ),
        );
    }

    public function billedTo(): BilledTo
    {
        return new BilledTo(
            name: $this->billedTo['name'],
            street: $this->billedTo['street'],
            location: $this->billedTo['location'],
            vatId: $this->billedTo['vatId'],
            country: $this->billedTo['country'],
        );
    }

    public function deliveredAt(): DateTime
    {
        return DateTime::fromDateString($this->deliveredAt);
    }
}
