<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Unit\Application;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Smoothie\FreelanceTools\Application\ApplicationInterface;
use Smoothie\FreelanceTools\Application\BillingDocuments;
use Smoothie\FreelanceTools\Application\CreateBillingDocument;
use Smoothie\FreelanceTools\Domain\Model\AdvancePayment;
use Smoothie\FreelanceTools\Domain\Model\Common\Amount;
use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;
use Smoothie\FreelanceTools\Domain\Model\FilterCriteria;
use Smoothie\FreelanceTools\Domain\Model\Group\ListOfTasksInProjects;
use Smoothie\FreelanceTools\Domain\Model\Invoice;
use Smoothie\FreelanceTools\Domain\Model\InvoiceItem;
use Smoothie\FreelanceTools\Domain\Model\Task;
use Smoothie\FreelanceTools\Domain\Model\Timing;
use Smoothie\FreelanceTools\Domain\Service\WorkTimeProcessor;
use Smoothie\FreelanceTools\Infrastructure\SystemClock;

#[Small]
#[CoversClass(BillingDocuments::class)]
#[CoversClass(CreateBillingDocument::class)]
#[CoversClass(InvoiceItem::class)]
#[UsesNamespace('Smoothie\\FreelanceTools\\Domain\\Model')]
#[UsesNamespace('Smoothie\\FreelanceTools\\Infrastructure')]
final class BillingDocumentsTest extends TestCase
{
    private ApplicationInterface&MockObject $application;
    private WorkTimeProcessor&MockObject $processor;
    private SystemClock $clock;

    protected function setUp(): void
    {
        $this->application = $this->createMock(ApplicationInterface::class);
        $this->processor = $this->createMock(WorkTimeProcessor::class);
        $this->clock = new SystemClock();
        $this->clock->setCurrentDate(DateTime::fromDateString('2026-08-15'));
    }

    public function testManualPackageAndFractionalHoursRemainDistinctPositions(): void
    {
        $invoice = $this->service()->create(new CreateBillingDocument(
            number: 'INV-1', customerId: 'customer', billingDate: '2025-03-03', deliveredAt: ['2025-02-01', '2025-02-28'],
            items: [new InvoiceItem(9, 1, 'Package', Amount::fromString('1000'), 'C62'), new InvoiceItem(9, '2.5', 'Consulting', Amount::fromString('70'))],
            reference: 'Agreement 42', documentNotes: 'Payment conditions', termOfPaymentInDays: 7,
        ));

        self::assertSame('1398.25', $invoice->payableAmount()->asDecimal());
        self::assertSame('1175.00', $invoice->netAmount()->asDecimal());
        self::assertSame([1, 2], array_map(static fn (InvoiceItem $item): int => $item->position(), $invoice->invoiceItems()));
        self::assertSame('C62', $invoice->invoiceItems()[0]->unit());
        self::assertSame('2.5', $invoice->invoiceItems()[1]->quantityAsDecimal());
        self::assertSame('175.00', $invoice->invoiceItems()[1]->priceTotal()->asDecimal());
        self::assertSame('pdf/timesheet/invoice.html.twig', $invoice->template());
        self::assertSame('Agreement 42', $invoice->reference());
        self::assertSame('Payment conditions', $invoice->documentNotes());
        self::assertSame('2025-03-10', $invoice->dueDate()->dueDate()->asDateString());
    }

    public function testMarcTemplateCanOverrideClassicDefault(): void
    {
        $invoice = $this->service()->create($this->command(templateId: 'marc'));

        self::assertSame('pdf/marc/invoice.html.twig', $invoice->template());
    }

    public function testTwoProjectsProduceDistinctExactQuantitiesWithoutChangingClock(): void
    {
        $before = $this->clock->currentTime();
        $this->application->expects(self::exactly(2))->method('getListOfTasks')->willReturnCallback(function (FilterCriteria $filter, WorkTimeProcessor $processor): ListOfTasksInProjects {
            self::assertSame($this->processor, $processor);
            $project = str_contains($filter->getQuery(), 'project = \'alpha\'') ? 'alpha' : 'beta';
            self::assertSame('(startDate >= \'2025-02-01\') AND (endDate <= \'2025-02-28\') AND (project = \''.$project.'\')', $filter->getQuery());

            return ListOfTasksInProjects::fromTasks([
                new Task($project, 'customer', 'Tracked work', [], [new Timing('2025-02-10 09:00:00', $project === 'alpha' ? '2025-02-10 11:30:00' : '2025-02-10 10:15:00')]),
                new Task($project, 'customer', 'More work', [], [new Timing('2025-02-11 09:00:00', '2025-02-11 09:30:00')]),
            ]);
        });
        $invoice = $this->service()->create(new CreateBillingDocument('INV-1', 'customer', '2025-03-03', [], projects: ['alpha', 'beta']));

        self::assertSame(['3', '1.75'], array_map(static fn (InvoiceItem $item): string => $item->quantityAsDecimal(), $invoice->invoiceItems()));
        self::assertSame([10800, 6300], array_map(static fn (InvoiceItem $item): ?int => $item->durationInSeconds(), $invoice->invoiceItems()));
        self::assertSame(['alpha — Development', 'beta — Support'], array_map(static fn (InvoiceItem $item): string => $item->description(), $invoice->invoiceItems()));
        self::assertSame(['210.00', '140.00'], array_map(static fn (InvoiceItem $item): string => $item->priceTotal()->asDecimal(), $invoice->invoiceItems()));
        self::assertSame(['2025-02-01', '2025-02-28'], array_map(static fn (DateTime $date): string => $date->asDateString(), $invoice->deliveredAt()));
        self::assertSame($before, $this->clock->currentTime());
    }

    public function testRepeatingHourFractionIsNotRoundedBeforePricing(): void
    {
        $organizations = $this->organizations();
        $organizations[0]['pricePerHour'] = 90.0;
        $this->application->method('getListOfTasks')->willReturn(ListOfTasksInProjects::fromTasks([
            new Task('alpha', 'customer', 'Tracked work', [], [new Timing('2025-02-10 09:00:00', '2025-02-10 10:00:07')]),
        ]));
        $invoice = $this->service($organizations)->create(new CreateBillingDocument('INV-1', 'customer', '2025-03-03', [], projects: ['alpha']));

        self::assertSame(3607, $invoice->invoiceItems()[0]->durationInSeconds());
        self::assertSame('90.18', $invoice->netAmount()->asDecimal());
    }

    #[DataProvider('unknownSelections')]
    public function testSelectedCustomerRejectsUnknownOrForeignProject(string $customer, array $projects): void
    {
        $this->application->expects(self::never())->method('getListOfTasks');
        $this->expectException(\InvalidArgumentException::class);

        $this->service()->create(new CreateBillingDocument('INV-1', $customer, '2025-03-03', ['2025-02-28'], [$this->item()], projects: $projects));
    }

    public static function unknownSelections(): array
    {
        return [['missing', []], ['customer', ['missing']], ['customer', ['foreign']]];
    }

    #[DataProvider('inconsistentSettings')]
    public function testCustomerProjectsMustAgreeOnBillingSettings(string $key, mixed $value): void
    {
        $organizations = $this->organizations();
        $organizations[1][$key] = $value;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Customer billing settings differ across projects: '.$key);

        $this->service($organizations)->create($this->command());
    }

    public static function inconsistentSettings(): array
    {
        return [['street', 'Other Street 9'], ['taxRate', 7.0], ['termOfPaymentInDays', 30], ['templateId', 'marc'], ['orderNumber', 'OTHER']];
    }

    public function testQuoteOnlyRendersWithoutBuildingAnEInvoice(): void
    {
        $this->application->expects(self::never())->method('buildAnEInvoice');
        $this->application->expects(self::once())->method('renderComponent')->with(self::callback(static fn (Invoice $invoice): bool => $invoice->documentType() === 'quote'))->willReturn('quote.pdf');

        self::assertSame('Angebot Nr. INV-1', $this->service()->generate($this->command(documentType: 'quote'))->title());
    }

    public function testAdvanceAndFinalAmountsUseRoundedVatAndReceivedGross(): void
    {
        $this->application->expects(self::exactly(2))->method('buildAnEInvoice')->willReturn('invoice.pdf');
        $this->application->expects(self::never())->method('renderComponent');
        $advance = $this->service()->generate($this->command(documentType: 'advance', items: [$this->item('749.50')]));
        self::assertSame('749.50', $advance->netAmount()->asDecimal());
        self::assertSame('142.41', $advance->vatAmount()->asDecimal());
        self::assertSame('891.91', $advance->grossAmount()->asDecimal());
        self::assertSame('0.00', $advance->prepaidAmount()->asDecimal());
        self::assertSame('891.91', $advance->payableAmount()->asDecimal());

        $payment = $this->payment();
        $final = $this->service()->generate($this->command(documentType: 'final', items: [$this->item('1499')], advancePayments: [$payment]));
        self::assertSame('1499.00', $final->netAmount()->asDecimal());
        self::assertSame('284.81', $final->vatAmount()->asDecimal());
        self::assertSame('1783.81', $final->grossAmount()->asDecimal());
        self::assertSame('891.91', $final->prepaidAmount()->asDecimal());
        self::assertSame('891.90', $final->payableAmount()->asDecimal());
        self::assertSame([$payment], $final->advancePayments());
        self::assertSame([$payment], $final->context()['payments']);
    }

    public function testMultipleReceiptsAreSummedWithoutReducingTheTaxBase(): void
    {
        $payments = [$this->payment(), $this->payment('ADV-2', net: '100', vat: '19', gross: '119')];
        $invoice = $this->service()->create($this->command(documentType: 'final', items: [$this->item('1499')], advancePayments: $payments));

        self::assertSame('1499.00', $invoice->netAmount()->asDecimal());
        self::assertSame('284.81', $invoice->vatAmount()->asDecimal());
        self::assertSame('1010.91', $invoice->prepaidAmount()->asDecimal());
        self::assertSame('772.90', $invoice->payableAmount()->asDecimal());
    }

    #[DataProvider('invalidReceipts')]
    public function testInvalidPaymentReferencesAreRejected(string $case): void
    {
        $payments = match ($case) {
            'duplicate' => [$this->payment(), $this->payment()],
            'self' => [$this->payment('INV-1')],
            'overpay' => [$this->payment(net: '2000', vat: '380', gross: '2380')],
            'future' => [$this->payment(received: '2025-03-04')],
            'tax' => [$this->payment(net: '100', vat: '7', gross: '107')],
        };
        $this->expectException(\InvalidArgumentException::class);

        $this->service()->create($this->command(documentType: 'final', items: [$this->item('1499')], advancePayments: $payments));
    }

    public static function invalidReceipts(): array
    {
        return [['duplicate'], ['self'], ['overpay'], ['future'], ['tax']];
    }

    #[DataProvider('nonFinalTypes')]
    public function testNonFinalDocumentsCannotDeductReceipts(string $type): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only final invoices can deduct payments.');

        $this->service()->create($this->command(documentType: $type, advancePayments: [$this->payment()]));
    }

    public static function nonFinalTypes(): array
    {
        return [['invoice'], ['advance'], ['quote']];
    }

    #[DataProvider('invalidRequests')]
    public function testRequestValidatesDatesAndRequiredInputs(array $overrides): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CreateBillingDocument(...array_replace(['number' => 'INV-1', 'customerId' => 'customer', 'billingDate' => '2025-03-03', 'deliveredAt' => ['2025-02-28'], 'items' => [$this->item()]], $overrides));
    }

    public static function invalidRequests(): array
    {
        return [
            [['billingDate' => '2025-02-30']], [['billingDate' => '03/03/2025']], [['deliveredAt' => ['2025-02-29']]],
            [['deliveredAt' => ['2025-03-01', '2025-02-01']]], [['startDate' => '2025-04-31']],
            [['deliveredAt' => []]], [['items' => []]], [['items' => ['invalid']]],
            [['number' => '../unsafe']], [['customerId' => ' ']], [['termOfPaymentInDays' => -1]],
            [['projects' => ['alpha', 'alpha']]], [['projects' => ['alpha'], 'documentType' => 'final']],
            [['deliveredAt' => [20250228]]], [['advancePayments' => ['invalid']]],
            [['items' => ['named' => 'invalid']]], [['projects' => [123]]],
        ];
    }

    #[DataProvider('invalidItems')]
    public function testItemsValidateQuantityPositionDescriptionPriceAndUnit(int $position, string $quantity, string $description, string $price, string $unit): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new InvoiceItem($position, $quantity, $description, Amount::fromString($price), $unit);
    }

    public static function invalidItems(): array
    {
        return [[1, '0', 'Work', '1', 'HUR'], [1, '-0.5', 'Work', '1', 'HUR'], [0, '1', 'Work', '1', 'HUR'], [1, '1', ' ', '1', 'HUR'], [1, '1', 'Work', '-1', 'HUR'], [1, '1', 'Work', '1', 'INVALID']];
    }

    private function command(string $documentType = 'invoice', ?string $templateId = null, ?array $items = null, array $advancePayments = []): CreateBillingDocument
    {
        return new CreateBillingDocument('INV-1', 'customer', '2025-03-03', ['2025-02-28'], $items ?? [$this->item()], $documentType, $templateId, advancePayments: $advancePayments);
    }

    private function item(string $price = '100'): InvoiceItem
    {
        return new InvoiceItem(1, 1, 'Package', Amount::fromString($price), 'C62');
    }

    private function payment(string $number = 'ADV-1', string $received = '2025-02-20', string $net = '749.50', string $vat = '142.41', string $gross = '891.91'): AdvancePayment
    {
        return new AdvancePayment($number, DateTime::fromDateString('2025-02-01'), DateTime::fromDateString($received), Amount::fromString($net), Amount::fromString($vat), Amount::fromString($gross));
    }

    private function organizations(): array
    {
        $organization = ['project' => 'alpha', 'customerId' => 'customer', 'name' => 'Customer GmbH', 'street' => 'Buyer Street 2', 'location' => '10115 Berlin', 'country' => 'DE', 'vatId' => 'DE123456789', 'description' => 'Development', 'taxRate' => 19.0, 'pricePerHour' => 70.0, 'termOfPaymentInDays' => 14];

        return [$organization, array_replace($organization, ['project' => 'beta', 'description' => 'Support', 'pricePerHour' => 80.0]), array_replace($organization, ['project' => 'foreign', 'customerId' => 'another-customer'])];
    }

    private function service(?array $organizations = null): BillingDocuments
    {
        return new BillingDocuments($this->application, $this->clock, $this->processor, $organizations ?? $this->organizations(), ['name' => 'Seller GmbH', 'street' => 'Seller Street 1', 'location' => '10115 Berlin', 'vatId' => 'DE987654321', 'country' => 'DE', 'phone' => '+493012345678', 'mail' => 'billing@example.com', 'web' => 'https://example.com', 'bank' => 'Bank', 'iban' => 'DE89370400440532013000', 'bic' => 'COBADEFFXXX']);
    }
}
