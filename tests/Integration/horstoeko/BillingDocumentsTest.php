<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Integration\horstoeko;

use horstoeko\zugferd\ZugferdSettings;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;
use Smoothie\FreelanceTools\Domain\Model\AdvancePayment;
use Smoothie\FreelanceTools\Domain\Model\BilledBy;
use Smoothie\FreelanceTools\Domain\Model\BilledTo;
use Smoothie\FreelanceTools\Domain\Model\Common\Amount;
use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;
use Smoothie\FreelanceTools\Domain\Model\Invoice;
use Smoothie\FreelanceTools\Domain\Model\InvoiceItem;
use Smoothie\FreelanceTools\Infrastructure\horstoeko\EInvoiceBuilder;
use Smoothie\FreelanceTools\Tests\Unit\Domain\InvoiceFactoryMethods;

#[Large]
#[CoversClass(EInvoiceBuilder::class)]
#[UsesNamespace('Smoothie\\FreelanceTools\\Domain\\Model')]
class BillingDocumentsTest extends TestCase
{
    use InvoiceFactoryMethods;

    public function testStandardInvoiceExportsMultipleDecimalLinesAndOneVatRate(): void
    {
        $invoice = $this->billingDocument();
        $xpath = $this->xml($invoice);

        self::assertSame('380', $xpath->evaluate('string(//rsm:ExchangedDocument/ram:TypeCode)'));
        self::assertSame('urn:cen.eu:en16931:2017', $xpath->evaluate('string(//ram:GuidelineSpecifiedDocumentContextParameter/ram:ID)'));
        self::assertSame(2.0, $xpath->evaluate('count(//ram:IncludedSupplyChainTradeLineItem)'));
        self::assertSame('HUR', $xpath->evaluate('string((//ram:BilledQuantity)[1]/@unitCode)'));
        self::assertSame(2.5, $xpath->evaluate('number((//ram:BilledQuantity)[1])'));
        self::assertSame('C62', $xpath->evaluate('string((//ram:BilledQuantity)[2]/@unitCode)'));
        self::assertSame(3.0, $xpath->evaluate('number((//ram:BilledQuantity)[2])'));
        self::assertSame(175.0, $xpath->evaluate('number((//ram:SpecifiedTradeSettlementLineMonetarySummation/ram:LineTotalAmount)[1])'));
        self::assertSame(30.0, $xpath->evaluate('number((//ram:SpecifiedTradeSettlementLineMonetarySummation/ram:LineTotalAmount)[2])'));
        self::assertSame(1.0, $xpath->evaluate('count(//ram:ApplicableHeaderTradeSettlement/ram:ApplicableTradeTax)'));
        self::assertSame(3.0, $xpath->evaluate('count(//ram:RateApplicablePercent[text() = "19.00"])'));
        $this->assertSummation($xpath, 205.0, 38.95, 243.95, 0.0, 243.95);
    }

    public function testSerializedOperandsPreserveFractionalPricesAndExactTrackedSeconds(): void
    {
        $invoice = $this->billingDocument();
        $invoice->addInvoiceItem(InvoiceItem::fromTrackedSeconds(3, 3607, 'Tracked support', Amount::fromString('90')));
        $invoice->addInvoiceItem(InvoiceItem::fromTrackedSeconds(4, 1, 'One second', Amount::fromString('3600')));
        $invoice->addInvoiceItem(new InvoiceItem(5, '100', 'Fractional unit price', Amount::fromString('1.2345'), 'C62'));
        $invoice->addInvoiceItem(new InvoiceItem(6, '0.333333', 'Decimal hours', Amount::fromString('90')));
        $xpath = $this->xml($invoice);

        self::assertSame('SEC', $xpath->evaluate('string((//ram:BilledQuantity)[3]/@unitCode)'));
        self::assertSame(3607.0, $xpath->evaluate('number((//ram:BilledQuantity)[3])'));
        self::assertSame(3600.0, $xpath->evaluate('number((//ram:NetPriceProductTradePrice)[3]/ram:BasisQuantity)'));
        self::assertSame('SEC', $xpath->evaluate('string((//ram:NetPriceProductTradePrice)[3]/ram:BasisQuantity/@unitCode)'));
        self::assertSame(90.18, $xpath->evaluate('number((//ram:SpecifiedTradeSettlementLineMonetarySummation/ram:LineTotalAmount)[3])'));
        self::assertSame(1.0, $xpath->evaluate('number((//ram:BilledQuantity)[4])'));
        self::assertSame(1.0, $xpath->evaluate('number((//ram:SpecifiedTradeSettlementLineMonetarySummation/ram:LineTotalAmount)[4])'));
        self::assertSame('1.234500', $xpath->evaluate('string((//ram:NetPriceProductTradePrice)[5]/ram:ChargeAmount)'));
        self::assertSame(123.45, $xpath->evaluate('number((//ram:SpecifiedTradeSettlementLineMonetarySummation/ram:LineTotalAmount)[5])'));
        self::assertSame('0.333333', $xpath->evaluate('string((//ram:BilledQuantity)[6])'));
        self::assertSame(30.0, $xpath->evaluate('number((//ram:SpecifiedTradeSettlementLineMonetarySummation/ram:LineTotalAmount)[6])'));
    }

    public function testSerializerSettingsAreRestoredAfterSuccessfulAndRejectedExports(): void
    {
        $quantityDecimals = ZugferdSettings::getQuantityDecimals();
        $specialDecimals = ZugferdSettings::getSpecialDecimalPlacesMaps();

        try {
            ZugferdSettings::setQuantityDecimals(4);
            ZugferdSettings::setUnitAmountDecimals(3);
            $configuredMap = ZugferdSettings::getSpecialDecimalPlacesMaps();
            (new EInvoiceBuilder())->build($this->billingDocument());
            self::assertSame(4, ZugferdSettings::getQuantityDecimals());
            self::assertSame($configuredMap, ZugferdSettings::getSpecialDecimalPlacesMaps());

            try {
                (new EInvoiceBuilder())->build($this->billingDocument(documentType: 'quote'));
                self::fail('Quote XML was accepted.');
            } catch (\InvalidArgumentException) {
                self::assertSame(4, ZugferdSettings::getQuantityDecimals());
                self::assertSame($configuredMap, ZugferdSettings::getSpecialDecimalPlacesMaps());
            }
        } finally {
            ZugferdSettings::setQuantityDecimals($quantityDecimals);
            ZugferdSettings::setSpecialDecimalPlacesMaps($specialDecimals);
        }
    }

    public function testAdvanceInvoiceUsesPrepaymentCodeWithoutDeductingItsOwnTotal(): void
    {
        $xpath = $this->xml($this->billingDocument(documentType: 'advance'));

        self::assertSame('386', $xpath->evaluate('string(//rsm:ExchangedDocument/ram:TypeCode)'));
        self::assertSame(0.0, $xpath->evaluate('count(//ram:InvoiceReferencedDocument)'));
        $this->assertSummation($xpath, 205.0, 38.95, 243.95, 0.0, 243.95);
    }

    public function testFinalInvoiceReferencesEveryAdvanceAndPreservesReceiptBreakdowns(): void
    {
        $payments = [
            $this->payment('ADV-001', '2025-01-02', '2025-01-10', '50.00', '9.50', '59.50'),
            $this->payment('ADV-002', '2025-01-12', '2025-01-20', '100.00', '19.00', '119.00'),
        ];
        $xpath = $this->xml($this->billingDocument(documentType: 'final', advancePayments: $payments));

        self::assertSame('380', $xpath->evaluate('string(//rsm:ExchangedDocument/ram:TypeCode)'));
        self::assertSame(1.0, $xpath->evaluate('count(//ram:InvoiceReferencedDocument)'));
        self::assertSame('ADV-001', $xpath->evaluate('string((//ram:InvoiceReferencedDocument)[1]/ram:IssuerAssignedID)'));
        self::assertSame('20250102', $xpath->evaluate('string((//ram:InvoiceReferencedDocument)[1]//qualified:DateTimeString)'));
        self::assertSame('Advance payment: invoice ADV-001 dated 2025-01-02; received 2025-01-10; net 50.00 EUR; VAT 9.50 EUR; gross 59.50 EUR.', $xpath->evaluate('string((//ram:IncludedNote/ram:Content)[1])'));
        self::assertSame('Advance payment: invoice ADV-002 dated 2025-01-12; received 2025-01-20; net 100.00 EUR; VAT 19.00 EUR; gross 119.00 EUR.', $xpath->evaluate('string((//ram:IncludedNote/ram:Content)[2])'));
        $this->assertSummation($xpath, 205.0, 38.95, 243.95, 178.5, 65.45);
    }

    public function testFullyPrepaidFinalInvoiceHasZeroPayableAmount(): void
    {
        $payment = $this->payment('ADV-001', '2025-01-02', '2025-01-10', '205.00', '38.95', '243.95');
        $xpath = $this->xml($this->billingDocument(documentType: 'final', advancePayments: [$payment]));

        $this->assertSummation($xpath, 205.0, 38.95, 243.95, 243.95, 0.0);
    }

    public function testOrderAgreementAndNotesRemainDistinctAndXmlEscaped(): void
    {
        $xpath = $this->xml($this->billingDocument(
            orderNumber: 'PO-42',
            reference: 'Agreement & <scope>',
            documentNotes: 'Notes & <conditions>',
        ));

        self::assertSame('PO-42', $xpath->evaluate('string(//ram:BuyerOrderReferencedDocument/ram:IssuerAssignedID)'));
        self::assertSame('Agreement & <scope>', $xpath->evaluate('string(//ram:ContractReferencedDocument/ram:IssuerAssignedID)'));
        self::assertSame('Notes & <conditions>', $xpath->evaluate('string(//ram:IncludedNote/ram:Content)'));
    }

    public function testAddressesUseTheirOwnPartyAndPreserveMultiwordCities(): void
    {
        $xpath = $this->xml($this->billingDocument());

        self::assertSame('1010', $xpath->evaluate('string(//ram:SellerTradeParty/ram:PostalTradeAddress/ram:PostcodeCode)'));
        self::assertSame('Wien Innere Stadt', $xpath->evaluate('string(//ram:SellerTradeParty/ram:PostalTradeAddress/ram:CityName)'));
        self::assertSame('AT', $xpath->evaluate('string(//ram:SellerTradeParty/ram:PostalTradeAddress/ram:CountryID)'));
        self::assertSame('61348', $xpath->evaluate('string(//ram:BuyerTradeParty/ram:PostalTradeAddress/ram:PostcodeCode)'));
        self::assertSame('Bad Homburg', $xpath->evaluate('string(//ram:BuyerTradeParty/ram:PostalTradeAddress/ram:CityName)'));
        self::assertSame('DE', $xpath->evaluate('string(//ram:BuyerTradeParty/ram:PostalTradeAddress/ram:CountryID)'));
        self::assertSame('Buyer Street 2', $xpath->evaluate('string(//ram:BuyerTradeParty/ram:PostalTradeAddress/ram:LineOne)'));
        self::assertSame('DE900000009', $xpath->evaluate('string(//ram:BuyerTradeParty/ram:SpecifiedTaxRegistration/ram:ID)'));
        self::assertSame(0.0, $xpath->evaluate('count(//ram:BuyerReference)'));
    }

    public function testActualDeliveryIsNotThePaymentDueDate(): void
    {
        $xpath = $this->xml($this->billingDocument());

        self::assertSame('20250131', $xpath->evaluate('string(//ram:ActualDeliverySupplyChainEvent//udt:DateTimeString)'));
        self::assertSame('20250303', $xpath->evaluate('string(//ram:SpecifiedTradePaymentTerms/ram:DueDateDateTime/udt:DateTimeString)'));
        self::assertSame(0.0, $xpath->evaluate('count(//ram:EffectiveSpecifiedPeriod)'));
    }

    public function testServicePeriodIsExportedWithoutClaimingAnActualDeliveryDate(): void
    {
        $xpath = $this->xml($this->billingDocument(deliveredAt: ['2025-01-01', '2025-01-31']));

        self::assertSame('20250101', $xpath->evaluate('string(//ram:BillingSpecifiedPeriod/ram:StartDateTime/udt:DateTimeString)'));
        self::assertSame('20250131', $xpath->evaluate('string(//ram:BillingSpecifiedPeriod/ram:EndDateTime/udt:DateTimeString)'));
        self::assertSame(0.0, $xpath->evaluate('count(//ram:ActualDeliverySupplyChainEvent)'));
    }

    public function testAdvanceDoesNotDeclareFutureServiceAsActualDelivery(): void
    {
        $xpath = $this->xml($this->billingDocument(documentType: 'advance'));

        self::assertSame(0.0, $xpath->evaluate('count(//ram:ActualDeliverySupplyChainEvent)'));
    }

    public function testQuoteXmlIsExplicitlyRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Quotes cannot be exported as e-invoices.');

        (new EInvoiceBuilder())->build($this->billingDocument(documentType: 'quote'));
    }

    private function xml(Invoice $invoice): \DOMXPath
    {
        $document = new \DOMDocument();
        self::assertTrue($document->loadXML((new EInvoiceBuilder())->build($invoice)));
        $xpath = new \DOMXPath($document);
        $xpath->registerNamespace('rsm', 'urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100');
        $xpath->registerNamespace('ram', 'urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100');
        $xpath->registerNamespace('udt', 'urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100');
        $xpath->registerNamespace('qualified', 'urn:un:unece:uncefact:data:standard:QualifiedDataType:100');

        return $xpath;
    }

    private function assertSummation(\DOMXPath $xpath, float $net, float $vat, float $gross, float $prepaid, float $payable): void
    {
        $path = '//ram:SpecifiedTradeSettlementHeaderMonetarySummation';
        self::assertSame($net, $xpath->evaluate('number('.$path.'/ram:LineTotalAmount)'));
        self::assertSame($net, $xpath->evaluate('number('.$path.'/ram:TaxBasisTotalAmount)'));
        self::assertSame($vat, $xpath->evaluate('number('.$path.'/ram:TaxTotalAmount)'));
        self::assertSame($gross, $xpath->evaluate('number('.$path.'/ram:GrandTotalAmount)'));
        self::assertSame($prepaid, $xpath->evaluate('number('.$path.'/ram:TotalPrepaidAmount)'));
        self::assertSame($payable, $xpath->evaluate('number('.$path.'/ram:DuePayableAmount)'));
    }

    private function payment(string $number, string $date, string $received, string $net, string $vat, string $gross): AdvancePayment
    {
        return new AdvancePayment(
            invoiceNumber: $number,
            invoiceDate: DateTime::fromDateString($date),
            receivedAt: DateTime::fromDateString($received),
            netAmount: Amount::fromString($net),
            vatAmount: Amount::fromString($vat),
            grossAmount: Amount::fromString($gross),
        );
    }

    /**
     * @param list<AdvancePayment> $advancePayments
     * @param list<string>|null $deliveredAt
     */
    private function billingDocument(string $documentType = 'invoice', array $advancePayments = [], string $orderNumber = '', string $reference = '', string $documentNotes = '', ?array $deliveredAt = null): Invoice
    {
        $invoice = new Invoice(
            title: $this->anInvoiceTitle(),
            projectId: $this->aProjectId(),
            invoiceId: $this->anInvoiceId(),
            description: $this->aDescription(),
            taxRate: $this->aTaxRate(),
            billingDate: $this->aBillingDate(),
            dueDate: $this->aDueDate(),
            billedBy: new BilledBy(
                name: 'Seller GmbH',
                street: 'Seller Street 1',
                location: '1010 Wien Innere Stadt',
                vatId: 'ATU12345678',
                country: 'AT',
                contactInformation: $this->aBilledBy()->contactInformation(),
                paymentInformation: $this->aBilledBy()->paymentInformation(),
            ),
            billedTo: new BilledTo(
                name: 'Buyer GmbH',
                street: 'Buyer Street 2',
                location: '61348 Bad Homburg',
                vatId: 'DE900000009',
                country: 'DE',
            ),
            deliveredAt: $deliveredAt === null ? $this->aDeliveredAt() : array_map(DateTime::fromDateString(...), $deliveredAt),
            orderNumber: $orderNumber,
            documentType: $documentType,
            reference: $reference,
            documentNotes: $documentNotes,
            advancePayments: $advancePayments,
        );
        $invoice->addInvoiceItem(new InvoiceItem(1, '2.5', 'Consulting', Amount::fromString('70.00'), 'HUR'));
        $invoice->addInvoiceItem(new InvoiceItem(2, '3', 'Materials', Amount::fromString('10.00'), 'C62'));

        return $invoice;
    }
}
