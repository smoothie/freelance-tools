<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Domain\Model;

use Smoothie\FreelanceTools\Domain\Model\Common\Amount;
use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;
use Webmozart\Assert\Assert;

class Invoice implements Component
{
    private Amount $netAmount;

    /**
     * @var InvoiceItem[]
     */
    private array $invoiceItems = [];

    /**
     * @param list<DateTime> $deliveredAt
     * @param list<AdvancePayment> $advancePayments
     */
    public function __construct(
        private string $title,
        private ProjectId $projectId,
        private InvoiceId $invoiceId,
        private string $description,
        private Amount $taxRate,
        private DateTime $billingDate,
        private DueDate $dueDate,
        private BilledBy $billedBy,
        private BilledTo $billedTo,
        private array $deliveredAt,
        private ?int $lastPageNumber = null,
        private string $orderNumber = '',
        private string $templateId = 'classic',
        private string $documentType = 'invoice',
        private string $reference = '',
        private string $documentNotes = '',
        private array $advancePayments = [],
    ) {
        Assert::greaterThan($taxRate->asFloat(), 0, 'A positive standard VAT rate is required.');
        Assert::lessThanEq($taxRate->asFloat(), 100);
        Assert::true($taxRate->isEqualTo($taxRate->rounded()), 'VAT rate must have at most two decimal places.');
        Assert::inArray($templateId, ['classic', 'marc']);
        Assert::inArray($documentType, ['invoice', 'advance', 'final', 'quote']);
        Assert::true($advancePayments === [] || $documentType === 'final', 'Only final invoices can deduct payments.');
        $references = [];
        foreach ($advancePayments as $payment) {
            Assert::isInstanceOf($payment, AdvancePayment::class);
            Assert::false(isset($references[$payment->invoiceNumber()]), 'Duplicate advance invoice reference.');
            Assert::notEq($payment->invoiceNumber(), $invoiceId->asString());
            Assert::lessThanEq($payment->receivedAt()->asDateString(), $billingDate->asDateString());
            Assert::same($payment->netAmount()->multiply($taxRate->multiply(0.01))->rounded()->asDecimal(), $payment->vatAmount()->asDecimal(), 'Advance VAT does not match the invoice VAT rate.');
            $references[$payment->invoiceNumber()] = true;
        }

        $this->netAmount = Amount::fromFloat(0.0);
    }

    public function netAmount(): Amount
    {
        return $this->netAmount;
    }

    public function vatAmount(): Amount
    {
        return $this->netAmount->multiply($this->taxRate->multiply(0.01))->rounded();
    }

    public function grossAmount(): Amount
    {
        return $this->netAmount()->add($this->vatAmount())->rounded();
    }

    /**
     * @return InvoiceItem[]
     */
    public function invoiceItems(): array
    {
        return $this->invoiceItems;
    }

    public function invoiceId(): InvoiceId
    {
        return $this->invoiceId;
    }

    public function projectId(): ProjectId
    {
        return $this->projectId;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function billingDate(): DateTime
    {
        return $this->billingDate;
    }

    public function dueDate(): DueDate
    {
        return $this->dueDate;
    }

    public function billedBy(): BilledBy
    {
        return $this->billedBy;
    }

    public function billedTo(): BilledTo
    {
        return $this->billedTo;
    }

    /**
     * @return list<DateTime>
     */
    public function deliveredAt(): array
    {
        return $this->deliveredAt;
    }

    public function taxRate(): Amount
    {
        return $this->taxRate;
    }

    public function addInvoiceItem(InvoiceItem $invoiceItem): void
    {
        foreach ($this->invoiceItems as $existing) {
            Assert::notEq($existing->position(), $invoiceItem->position(), 'Duplicate invoice position.');
        }
        $this->netAmount = $this->netAmount->add($invoiceItem->priceTotal())->rounded();

        $this->invoiceItems[] = $invoiceItem;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function template(): string
    {
        return $this->templateId === 'marc'
            ? 'pdf/marc/invoice.html.twig'
            : 'pdf/timesheet/invoice.html.twig';
    }

    public function context(): array
    {
        return [
            'title' => $this->title(),
            'project' => $this->projectId(),
            'netAmount' => $this->netAmount(),
            'vatAmount' => $this->vatAmount(),
            'grossAmount' => $this->grossAmount(),
            'invoiceItems' => $this->invoiceItems(),
            'invoiceId' => $this->invoiceId(),
            'description' => $this->description(),
            'taxRate' => $this->taxRate(),
            'billingDate' => $this->billingDate(),
            'dueDate' => $this->dueDate(),
            'sender' => $this->billedBy(),
            'recipient' => $this->billedTo(),
            'deliveredAt' => $this->deliveredAt(),
            'lastPageNumber' => $this->lastPageNumber,
            'orderNumber' => $this->orderNumber(),
            'documentType' => $this->documentType(),
            'reference' => $this->reference(),
            'documentNotes' => $this->documentNotes(),
            'payments' => $this->advancePayments(),
            'prepaidAmount' => $this->prepaidAmount(),
            'payableAmount' => $this->payableAmount(),
        ];
    }

    public function fileName(string $extension = '', ?DateTime $now = null): string
    {
        if ($now === null) {
            $now = DateTime::fromDateTime(new \DateTimeImmutable('now'));
        }

        return \sprintf(
            '%s%s',
            implode(' - ', array_filter([
                $now->asDateString(),
                $this->billedTo->name(),
                str_replace('.', '', $this->title),
                $this->billedBy->name(),
            ])),
            empty($extension) ? '' : \sprintf('.%s', $extension),
        );
    }

    public function setPageNumber(int $pageNumber): void
    {
        $this->lastPageNumber = $pageNumber;
    }

    public function documentType(): string
    {
        return $this->documentType;
    }

    public function reference(): string
    {
        return $this->reference;
    }

    public function documentNotes(): string
    {
        return $this->documentNotes;
    }

    /**
     * @return list<AdvancePayment>
     */
    public function advancePayments(): array
    {
        return $this->advancePayments;
    }

    public function prepaidAmount(): Amount
    {
        $amount = Amount::fromFloat(0.0);
        foreach ($this->advancePayments as $payment) {
            $amount = $amount->add($payment->grossAmount());
        }

        return $amount;
    }

    public function payableAmount(): Amount
    {
        $amount = $this->grossAmount()->subtract($this->prepaidAmount())->rounded();
        Assert::greaterThanEq($amount->asFloat(), 0, 'Payments exceed the invoice total.');

        return $amount;
    }

    public function orderNumber(): string
    {
        return $this->orderNumber;
    }
}
