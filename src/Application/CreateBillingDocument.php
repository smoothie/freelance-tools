<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Application;

use Smoothie\FreelanceTools\Domain\Model\AdvancePayment;
use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;
use Smoothie\FreelanceTools\Domain\Model\InvoiceItem;
use Webmozart\Assert\Assert;

final readonly class CreateBillingDocument
{
    /**
     * @param list<string> $deliveredAt
     * @param list<InvoiceItem> $items
     * @param list<AdvancePayment> $advancePayments
     * @param list<string> $projects
     */
    public function __construct(
        public string $number,
        public string $customerId,
        public string $billingDate,
        public array $deliveredAt,
        public array $items = [],
        public string $documentType = 'invoice',
        public ?string $templateId = null,
        public string $reference = '',
        public string $documentNotes = '',
        public array $advancePayments = [],
        public array $projects = [],
        public string $performancePeriod = 'LAST_MONTH',
        public ?string $startDate = null,
        public ?int $termOfPaymentInDays = null,
    ) {
        Assert::regex($number, '/^[A-Za-z0-9][A-Za-z0-9._-]{0,79}$/', 'Use a filename-safe document number.');
        Assert::notWhitespaceOnly($customerId);
        Assert::inArray($documentType, ['invoice', 'advance', 'final', 'quote']);
        Assert::nullOrInArray($templateId, ['classic', 'marc']);
        Assert::true($deliveredAt !== [] || $projects !== [], 'Manual documents require a service date or period.');
        Assert::maxCount($deliveredAt, 2);
        self::validateStrings($deliveredAt);
        self::validateStrings($projects);
        self::validateInstances($items, InvoiceItem::class);
        self::validateInstances($advancePayments, AdvancePayment::class);
        Assert::allNotEmpty($projects);
        Assert::same(\count($projects), \count(array_unique($projects)), 'Duplicate project.');
        Assert::true($items !== [] || $projects !== [], 'At least one item or project is required.');
        Assert::true($projects === [] || $documentType === 'invoice', 'Tracked projects are only supported for ordinary invoices.');
        Assert::nullOrGreaterThanEq($termOfPaymentInDays, 0);
        self::validateDate($billingDate);
        foreach ($deliveredAt as $date) {
            self::validateDate($date);
        }
        if (\count($deliveredAt) === 2) {
            Assert::lessThanEq($deliveredAt[0], $deliveredAt[1]);
        }
        if ($startDate !== null) {
            self::validateDate($startDate);
        }
    }

    /**
     * @param array<array-key, mixed> $values
     * @param class-string $class
     */
    private static function validateInstances(array $values, string $class): void
    {
        Assert::true(array_is_list($values), 'A list is required.');
        Assert::allIsInstanceOf($values, $class);
    }

    /**
     * @param array<array-key, mixed> $values
     */
    private static function validateStrings(array $values): void
    {
        Assert::true(array_is_list($values), 'A list is required.');
        Assert::allString($values);
    }

    private static function validateDate(string $date): void
    {
        Assert::regex($date, '/^\d{4}-\d{2}-\d{2}$/');
        Assert::same($date, DateTime::fromDateString($date)->asDateString(), 'Invalid calendar date.');
    }
}
