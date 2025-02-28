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
        private DateTime $deliveredAt,
        private ?int $lastPageNumber = null,
    ) {
        Assert::notEmpty($taxRate->asFloat(), 'Tax rate should not be empty');

        $this->netAmount = Amount::fromFloat(0.0);
    }

    public function netAmount(): Amount
    {
        return $this->netAmount;
    }

    public function vatAmount(): Amount
    {
        $taxRateInPercent = ($this->taxRate->asFloat() / 100);

        return $this->netAmount->multiply($taxRateInPercent);
    }

    public function grossAmount(): Amount
    {
        $taxRate = 1 + ($this->taxRate->asFloat() / 100);

        return $this->netAmount->multiply($taxRate);
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

    public function deliveredAt(): DateTime
    {
        return $this->deliveredAt;
    }

    public function taxRate(): Amount
    {
        return $this->taxRate;
    }

    public function addInvoiceItem(InvoiceItem $invoiceItem): void
    {
        $this->netAmount = $this->netAmount->add($invoiceItem->priceTotal());

        $this->invoiceItems[] = $invoiceItem;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function template(): string
    {
        return 'pdf/timesheet/invoice.html.twig';
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
}
