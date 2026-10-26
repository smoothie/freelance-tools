<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Unit\Domain;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Smoothie\FreelanceTools\Domain\Model\AdvancePayment;
use Smoothie\FreelanceTools\Domain\Model\Common\Amount;
use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;

#[Small]
#[CoversClass(AdvancePayment::class)]
#[UsesClass(Amount::class)]
#[UsesClass(DateTime::class)]
final class AdvancePaymentTest extends TestCase
{
    public function testReceiptPreservesReferenceDatesAndExactCentAmounts(): void
    {
        $date = DateTime::fromDateString('2025-02-01');
        $received = DateTime::fromDateString('2025-02-20');
        $net = Amount::fromString('749.50');
        $vat = Amount::fromString('142.41');
        $gross = Amount::fromString('891.91');
        $payment = new AdvancePayment('ADV-1', $date, $received, $net, $vat, $gross);

        self::assertSame('ADV-1', $payment->invoiceNumber());
        self::assertSame($date, $payment->invoiceDate());
        self::assertSame($received, $payment->receivedAt());
        self::assertSame($net, $payment->netAmount());
        self::assertSame($vat, $payment->vatAmount());
        self::assertSame($gross, $payment->grossAmount());
    }

    public function testReceiptMayArriveOnItsInvoiceDate(): void
    {
        $date = DateTime::fromDateString('2025-02-01');
        $payment = new AdvancePayment('ADV-1', $date, $date, Amount::fromString('100'), Amount::fromString('19'), Amount::fromString('119'));

        self::assertSame('119.00', $payment->grossAmount()->asDecimal());
    }

    #[DataProvider('invalidReceipts')]
    public function testInvalidReceiptIsRejected(string $number, string $received, string $net, string $vat, string $gross): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new AdvancePayment($number, DateTime::fromDateString('2025-02-01'), DateTime::fromDateString($received), Amount::fromString($net), Amount::fromString($vat), Amount::fromString($gross));
    }

    public static function invalidReceipts(): array
    {
        return [
            [' ', '2025-02-20', '100', '19', '119'],
            ['ADV-1', '2025-01-31', '100', '19', '119'],
            ['ADV-1', '2025-02-20', '0', '0', '0'],
            ['ADV-1', '2025-02-20', '-1', '20', '19'],
            ['ADV-1', '2025-02-20', '100', '-1', '99'],
            ['ADV-1', '2025-02-20', '100', '19', '120'],
            ['ADV-1', '2025-02-20', '100.001', '18.999', '119'],
            ['ADV-1', '2025-02-20', '100', '19.001', '119.001'],
        ];
    }
}
