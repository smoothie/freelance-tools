<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain;

use App\Domain\Model\Common\Amount;
use App\Domain\Model\InvoiceItem;
use App\Tests\BasicTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;

#[Small]
#[Group('domain')]
#[Group('invoice')]
#[CoversClass(InvoiceItem::class)]
#[UsesClass(Amount::class)]
class InvoiceItemTest extends BasicTestCase
{
    use InvoiceFactoryMethods;

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
