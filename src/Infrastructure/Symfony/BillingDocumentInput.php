<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Infrastructure\Symfony;

use Smoothie\FreelanceTools\Application\CreateBillingDocument;
use Smoothie\FreelanceTools\Domain\Model\AdvancePayment;
use Smoothie\FreelanceTools\Domain\Model\Common\Amount;
use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;
use Smoothie\FreelanceTools\Domain\Model\InvoiceItem;
use Webmozart\Assert\Assert;

final class BillingDocumentInput
{
    /**
     * @param array<string, mixed> $input
     */
    public function parse(array $input): CreateBillingDocument
    {
        $this->keys($input, ['number', 'customerId', 'billingDate', 'deliveredAt', 'items', 'documentType', 'templateId', 'reference', 'documentNotes', 'advancePayments', 'projects', 'performancePeriod', 'startDate', 'termOfPaymentInDays']);
        $items = [];
        foreach ($this->list($this->optional($input, 'items', []), 'items') as $index => $item) {
            Assert::isArray($item, 'Each item must be a mapping.');
            $this->keys($item, ['description', 'quantity', 'unit', 'pricePerItem']);
            $items[] = new InvoiceItem(
                position: $index + 1,
                quantity: $this->decimal($item['quantity'] ?? null, 'quantity'),
                description: $this->string($item['description'] ?? null, 'description'),
                pricePerItem: Amount::fromString($this->decimal($item['pricePerItem'] ?? null, 'pricePerItem')),
                unit: $this->string($this->optional($item, 'unit', 'HUR'), 'unit'),
            );
        }
        $payments = [];
        foreach ($this->list($this->optional($input, 'advancePayments', []), 'advancePayments') as $payment) {
            Assert::isArray($payment, 'Each advance payment must be a mapping.');
            $this->keys($payment, ['invoiceNumber', 'invoiceDate', 'receivedAt', 'netAmount', 'vatAmount', 'grossAmount']);
            $payments[] = new AdvancePayment(
                invoiceNumber: $this->string($payment['invoiceNumber'] ?? null, 'invoiceNumber'),
                invoiceDate: $this->date($payment['invoiceDate'] ?? null, 'invoiceDate'),
                receivedAt: $this->date($payment['receivedAt'] ?? null, 'receivedAt'),
                netAmount: Amount::fromString($this->decimal($payment['netAmount'] ?? null, 'netAmount')),
                vatAmount: Amount::fromString($this->decimal($payment['vatAmount'] ?? null, 'vatAmount')),
                grossAmount: Amount::fromString($this->decimal($payment['grossAmount'] ?? null, 'grossAmount')),
            );
        }
        $deliveredAt = $this->strings($this->optional($input, 'deliveredAt', []), 'deliveredAt');
        $projects = $this->strings($this->optional($input, 'projects', []), 'projects');
        $term = $input['termOfPaymentInDays'] ?? null;
        Assert::nullOrInteger($term, 'termOfPaymentInDays must be an integer or null.');

        return new CreateBillingDocument(
            number: $this->string($input['number'] ?? null, 'number'),
            customerId: $this->string($input['customerId'] ?? null, 'customerId'),
            billingDate: $this->string($input['billingDate'] ?? null, 'billingDate'),
            deliveredAt: $deliveredAt,
            items: $items,
            documentType: $this->string($this->optional($input, 'documentType', 'invoice'), 'documentType'),
            templateId: $this->nullableString($input['templateId'] ?? null, 'templateId'),
            reference: $this->string($this->optional($input, 'reference', ''), 'reference'),
            documentNotes: $this->string($this->optional($input, 'documentNotes', ''), 'documentNotes'),
            advancePayments: $payments,
            projects: $projects,
            performancePeriod: $this->string($this->optional($input, 'performancePeriod', 'LAST_MONTH'), 'performancePeriod'),
            startDate: $this->nullableString($input['startDate'] ?? null, 'startDate'),
            termOfPaymentInDays: $term,
        );
    }

    /**
     * @param array<array-key, mixed> $input
     * @param list<string> $allowed
     */
    private function keys(array $input, array $allowed): void
    {
        foreach (array_keys($input) as $key) {
            if (! \in_array($key, $allowed, true)) {
                throw new \InvalidArgumentException('Unknown input key: '.$key);
            }
        }
    }

    /**
     * @param array<array-key, mixed> $input
     */
    private function optional(array $input, string $key, mixed $default): mixed
    {
        return \array_key_exists($key, $input) ? $input[$key] : $default;
    }

    private function string(mixed $value, string $key): string
    {
        Assert::string($value, $key.' must be a string.');

        return $value;
    }

    private function nullableString(mixed $value, string $key): ?string
    {
        return $value === null ? null : $this->string($value, $key);
    }

    private function decimal(mixed $value, string $key): string
    {
        $value = $this->string($value, $key);
        Assert::regex($value, '/^-?\d+(?:\.\d+)?$/D', $key.' must be a decimal string, for example "1499.00".');

        return $value;
    }

    /**
     * @return list<mixed>
     */
    private function list(mixed $value, string $key): array
    {
        Assert::isArray($value, $key.' must be a list.');
        Assert::true(array_is_list($value), $key.' must be a list.');

        return $value;
    }

    /**
     * @return list<string>
     */
    private function strings(mixed $value, string $key): array
    {
        $values = $this->list($value, $key);
        Assert::allString($values, $key.' must contain only strings.');

        return $values;
    }

    private function date(mixed $value, string $key): DateTime
    {
        $value = $this->string($value, $key);
        Assert::regex($value, '/^\d{4}-\d{2}-\d{2}$/D', $key.' must use YYYY-MM-DD.');
        $date = DateTime::fromDateString($value);
        Assert::same($value, $date->asDateString(), $key.' must be a valid calendar date.');

        return $date;
    }
}
