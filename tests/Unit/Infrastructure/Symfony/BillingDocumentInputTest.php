<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Unit\Infrastructure\Symfony;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Smoothie\FreelanceTools\Infrastructure\Symfony\BillingDocumentInput;

#[Small]
#[CoversClass(BillingDocumentInput::class)]
final class BillingDocumentInputTest extends TestCase
{
    public function testManualDocumentPreservesExactDecimalsAndDefaults(): void
    {
        $document = (new BillingDocumentInput())->parse(self::input());

        self::assertSame('invoice', $document->documentType);
        self::assertNull($document->templateId);
        self::assertSame('1499.00', $document->items[0]->pricePerItem()->asDecimal());
        self::assertSame('1', $document->items[0]->quantityAsDecimal());
        self::assertSame(1, $document->items[0]->position());
        self::assertSame('C62', $document->items[0]->unit());
    }

    public function testTrackedProjectsMayOmitDeliveryDatesAndItems(): void
    {
        $input = self::input();
        unset($input['items'], $input['deliveredAt']);
        $input['projects'] = ['website'];
        $document = (new BillingDocumentInput())->parse($input);

        self::assertSame([], $document->deliveredAt);
        self::assertSame(['website'], $document->projects);
        self::assertSame('LAST_MONTH', $document->performancePeriod);
    }

    public function testFinalDocumentParsesAdvancePayments(): void
    {
        $document = (new BillingDocumentInput())->parse([...self::input(), 'documentType' => 'final', 'advancePayments' => [self::payment()]]);

        self::assertSame('749.50', $document->advancePayments[0]->netAmount()->asDecimal());
        self::assertSame('142.41', $document->advancePayments[0]->vatAmount()->asDecimal());
        self::assertSame('891.91', $document->advancePayments[0]->grossAmount()->asDecimal());
        self::assertSame('2026-01-15', $document->advancePayments[0]->receivedAt()->asDateString());
    }

    #[DataProvider('invalidInputs')]
    public function testInvalidInputIsRejected(array $changes): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new BillingDocumentInput())->parse(array_replace(self::input(), $changes));
    }

    /**
     * @return iterable<string, array{array<string, mixed>}>
     */
    public static function invalidInputs(): iterable
    {
        yield 'unknown key' => [['typo' => true]];
        yield 'wrong number type' => [['number' => 42]];
        yield 'bad date' => [['billingDate' => '2026-02-30']];
        yield 'manual delivery missing' => [['deliveredAt' => []]];
        yield 'delivery not list' => [['deliveredAt' => ['start' => '2026-01-01']]];
        yield 'delivery integer' => [['deliveredAt' => [20260101]]];
        yield 'null items' => [['items' => null]];
        yield 'items not list' => [['items' => ['first' => self::input()['items'][0]]]];
        yield 'unknown item key' => [['items' => [[...self::input()['items'][0], 'price' => '5']]]];
        foreach ([1, 1.5, '1e2', '1,5', '1.5bad', ' 1.5', '', null] as $index => $value) {
            yield 'quantity '.$index => [['items' => [[...self::input()['items'][0], 'quantity' => $value]]]];
            yield 'price '.$index => [['items' => [[...self::input()['items'][0], 'pricePerItem' => $value]]]];
        }
        yield 'unknown type' => [['documentType' => 'receipt']];
        yield 'unknown template' => [['templateId' => 'other']];
        yield 'term float' => [['termOfPaymentInDays' => 14.0]];
        yield 'term string' => [['termOfPaymentInDays' => '14']];
        yield 'null reference' => [['reference' => null]];
        yield 'projects not list' => [['projects' => 'website']];
        yield 'bad payment date' => [['advancePayments' => [[...self::payment(), 'invoiceDate' => '2026-02-30']]]];
        yield 'payment date timestamp' => [['advancePayments' => [[...self::payment(), 'receivedAt' => '2026-01-15 00:00:00']]]];
        yield 'payment unknown key' => [['advancePayments' => [[...self::payment(), 'extra' => true]]]];
        yield 'payment float' => [['advancePayments' => [[...self::payment(), 'netAmount' => 749.5]]]];
    }

    /**
     * @return array<string, mixed>
     */
    private static function input(): array
    {
        return [
            'number' => 'INV-2026-001',
            'customerId' => 'example-customer',
            'billingDate' => '2026-02-01',
            'deliveredAt' => ['2026-01-01', '2026-01-31'],
            'items' => [['description' => 'Website', 'quantity' => '1', 'unit' => 'C62', 'pricePerItem' => '1499.00']],
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function payment(): array
    {
        return [
            'invoiceNumber' => 'ADV-2026-001',
            'invoiceDate' => '2026-01-01',
            'receivedAt' => '2026-01-15',
            'netAmount' => '749.50',
            'vatAmount' => '142.41',
            'grossAmount' => '891.91',
        ];
    }
}
