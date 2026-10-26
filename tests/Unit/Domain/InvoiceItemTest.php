<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Unit\Domain;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use Smoothie\FreelanceTools\Domain\Model\Common\Amount;
use Smoothie\FreelanceTools\Domain\Model\InvoiceItem;
use Smoothie\FreelanceTools\Tests\BasicTestCase;

#[Small]
#[Group('domain')]
#[Group('invoice')]
#[CoversClass(InvoiceItem::class)]
#[UsesClass(Amount::class)]
class InvoiceItemTest extends BasicTestCase
{
    use InvoiceFactoryMethods;

    public function testDecimalQuantitiesAreCanonicalAndTotalsAreRoundedOnce(): void
    {
        $item = new InvoiceItem(1, '01.2500', 'Consulting', Amount::fromString('10.004'), 'C62');

        self::assertSame('1.25', $item->quantityAsDecimal());
        self::assertSame(1.25, $item->quantity());
        self::assertSame('C62', $item->unit());
        self::assertSame('12.51', $item->priceTotal()->asDecimal());
        self::assertSame('10.004', $item->pricePerItem()->asDecimal(3));

        $item = new InvoiceItem(1, '1', 'Consulting', Amount::fromString('1.004999'));

        self::assertSame(1, $item->quantity());
        self::assertSame('1.00', $item->priceTotal()->asDecimal());
    }

    public function testFloatAndMaximumDecimalQuantitiesRemainCompatible(): void
    {
        $item = new InvoiceItem(1, 1.5, 'Consulting', Amount::fromString('0'));

        self::assertSame(1.5, $item->quantity());
        self::assertSame('1.5', $item->quantityAsDecimal());
        self::assertSame('0.00', $item->priceTotal()->asDecimal());

        $item = new InvoiceItem(1, '999999.999999', 'Consulting', Amount::fromString('0.000001'));

        self::assertSame('999999.999999', $item->quantityAsDecimal());
        self::assertSame('1.00', $item->priceTotal()->asDecimal());
    }

    public function testTrackedTimeIsPricedFromExactSecondsBeforeRounding(): void
    {
        foreach ([[1200, '90', '30.00'], [3607, '90', '90.18'], [1, '3600', '1.00'], [8400, '90', '210.00'], [1, '90', '0.03']] as [$seconds, $rate, $expected]) {
            $item = InvoiceItem::fromTrackedSeconds(9, $seconds, 'Tracked support', Amount::fromString($rate));
            $renumbered = $item->withPosition(1);

            self::assertSame($expected, $item->priceTotal()->asDecimal());
            self::assertSame($seconds, $item->durationInSeconds());
            self::assertSame($seconds, $renumbered->durationInSeconds());
            self::assertSame($expected, $renumbered->priceTotal()->asDecimal());
            self::assertSame(9, $item->position());
            self::assertSame(1, $renumbered->position());
        }
    }

    public function testInvalidTrackedSecondsAndPositionsAreRejected(): void
    {
        foreach ([0, -1, 3600000000] as $seconds) {
            try {
                InvoiceItem::fromTrackedSeconds(1, $seconds, 'Tracked support', Amount::fromString('90'));
                self::fail('Invalid tracked duration was accepted.');
            } catch (\InvalidArgumentException $exception) {
                self::assertNotSame('', $exception->getMessage());
            }
        }
        $this->expectException(\InvalidArgumentException::class);
        (new InvoiceItem(1, 1, 'Support', Amount::fromString('90')))->withPosition(0);
    }

    public function testInvalidInvoiceItemsAreRejected(): void
    {
        $cases = [
            [1, 0, 'Consulting', '1', 'HUR'],
            [1, -1, 'Consulting', '1', 'HUR'],
            [1, '-0.000001', 'Consulting', '1', 'HUR'],
            [1, 1, 'Consulting', '-0.0000000001', 'HUR'],
            [0, 1, 'Consulting', '1', 'HUR'],
            [-1, 1, 'Consulting', '1', 'HUR'],
            [1, 1, " \t\n", '1', 'HUR'],
            [1, 1, 'Consulting', '1', 'DAY'],
            [1, 1, 'Consulting', '1', 'hur'],
            [1, '0.0000001', 'Consulting', '1', 'HUR'],
            [1, 1, 'Consulting', '0.0000001', 'HUR'],
            [1, 1000000, 'Consulting', '1', 'HUR'],
            [1, 1, 'Consulting', '1000000', 'HUR'],
            [1, '9007199254740993.01', 'Consulting', '1', 'HUR'],
        ];

        foreach ($cases as [$position, $quantity, $description, $price, $unit]) {
            try {
                new InvoiceItem($position, $quantity, $description, Amount::fromString($price), $unit);
                self::fail('Invalid invoice item was accepted.');
            } catch (\InvalidArgumentException $exception) {
                self::assertNotSame('', $exception->getMessage());
            }
        }
    }

    public function testThatAnInvoiceItem(): void
    {
        $pricePerItem = Amount::fromFloat(10.0);

        $invoiceItem = new InvoiceItem(
            position: 3,
            quantity: 5,
            description: $this->aDescription(),
            pricePerItem: $pricePerItem,
        );

        self::assertSame(3, $invoiceItem->position());
        self::assertSame(5, $invoiceItem->quantity());
        self::assertSame($this->aDescription(), $invoiceItem->description());
        self::assertSame('HUR', $invoiceItem->unit());
        self::assertSame($pricePerItem, $invoiceItem->pricePerItem());
        self::assertEqualsCanonicalizing(Amount::fromFloat(50.0), $invoiceItem->priceTotal());
    }
}
