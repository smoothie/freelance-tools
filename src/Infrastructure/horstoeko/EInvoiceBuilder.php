<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Infrastructure\horstoeko;

use horstoeko\zugferd\codelists\ZugferdCurrencyCodes;
use horstoeko\zugferd\codelists\ZugferdDocumentType;
use horstoeko\zugferd\codelists\ZugferdElectronicAddressScheme;
use horstoeko\zugferd\codelists\ZugferdInvoiceType;
use horstoeko\zugferd\codelists\ZugferdPaymentMeans;
use horstoeko\zugferd\codelists\ZugferdReferenceCodeQualifiers;
use horstoeko\zugferd\codelists\ZugferdVatCategoryCodes;
use horstoeko\zugferd\codelists\ZugferdVatTypeCodes;
use horstoeko\zugferd\ZugferdDocumentBuilder;
use horstoeko\zugferd\ZugferdProfiles;
use horstoeko\zugferd\ZugferdSettings;
use horstoeko\zugferd\ZugferdXsdValidator;
use Smoothie\FreelanceTools\Domain\Model\Invoice;

class EInvoiceBuilder
{
    public function build(Invoice $invoice): string
    {
        $quantityDecimals = ZugferdSettings::getQuantityDecimals();
        $amountDecimals = ZugferdSettings::getAmountDecimals();
        $percentDecimals = ZugferdSettings::getPercentDecimals();
        $specialDecimals = ZugferdSettings::getSpecialDecimalPlacesMaps();

        try {
            ZugferdSettings::setQuantityDecimals(6);
            ZugferdSettings::setUnitAmountDecimals(6);
            ZugferdSettings::setAmountDecimals(2);
            ZugferdSettings::setPercentDecimals(2);

            return $this->buildDocument($invoice);
        } finally {
            ZugferdSettings::setQuantityDecimals($quantityDecimals);
            ZugferdSettings::setAmountDecimals($amountDecimals);
            ZugferdSettings::setPercentDecimals($percentDecimals);
            ZugferdSettings::setSpecialDecimalPlacesMaps($specialDecimals);
        }
    }

    private function buildDocument(Invoice $invoice): string
    {
        $documentTypeCode = match ($invoice->documentType()) {
            'invoice', 'final' => ZugferdDocumentType::COMMERCIAL_INVOICE,
            'advance' => ZugferdInvoiceType::PREPAYMENTINVOICE,
            'quote' => throw new \InvalidArgumentException('Quotes cannot be exported as e-invoices.'),
            default => throw new \InvalidArgumentException('Unsupported billing document type.'),
        };

        if ($invoice->invoiceItems() === []) {
            throw new \InvalidArgumentException('At least one invoice position is required.');
        }

        if ($invoice->grossAmount()->asFloat() >= 10000000000000) {
            throw new \InvalidArgumentException('Document total must be below 10000000000000 EUR.');
        }

        $builder = ZugferdDocumentBuilder::createNew(ZugferdProfiles::PROFILE_EN16931);

        $builder->setDocumentBusinessProcess('Invoice');
        $builder->setDocumentInformation(
            documentNo: $invoice->invoiceId()->asString(),
            documentTypeCode: $documentTypeCode,
            documentDate: new \DateTime($invoice->billingDate()->asString()),
            invoiceCurrency: ZugferdCurrencyCodes::EURO,
            documentName: $invoice->title(),
            documentLanguage: 'de_DE',
        );
        if ($invoice->orderNumber() !== '') {
            $builder->setDocumentBuyerOrderReferencedDocument($invoice->orderNumber());
        }
        if ($invoice->reference() !== '') {
            $builder->setDocumentContractReferencedDocument($invoice->reference());
        }
        if ($invoice->documentNotes() !== '') {
            $builder->addDocumentNote($invoice->documentNotes());
        }
        $payments = $invoice->advancePayments();
        if ($payments !== []) {
            $builder->addDocumentInvoiceReferencedDocument(
                issuerAssignedId: $payments[0]->invoiceNumber(),
                issueDate: new \DateTime($payments[0]->invoiceDate()->asString()),
            );
        }
        foreach ($payments as $payment) {
            $builder->addDocumentNote(\sprintf(
                'Advance payment: invoice %s dated %s; received %s; net %s EUR; VAT %s EUR; gross %s EUR.',
                $payment->invoiceNumber(),
                $payment->invoiceDate()->asDateString(),
                $payment->receivedAt()->asDateString(),
                $payment->netAmount()->asDecimal(),
                $payment->vatAmount()->asDecimal(),
                $payment->grossAmount()->asDecimal(),
            ));
        }
        $deliveredAt = $invoice->deliveredAt();
        if (\count($deliveredAt) === 1 && $invoice->documentType() !== 'advance') {
            $builder->setDocumentSupplyChainEvent(new \DateTime($deliveredAt[0]->asString()));
        }
        if (\count($deliveredAt) === 2) {
            $builder->setDocumentBillingPeriod(
                startDate: new \DateTime($deliveredAt[0]->asString()),
                endDate: new \DateTime($deliveredAt[1]->asString()),
                description: null,
            );
        }

        $builder->setDocumentSeller($invoice->billedBy()->name());
        $builder->addDocumentSellerTaxRegistration(
            ZugferdReferenceCodeQualifiers::VAT_REGI_NUMB,
            $invoice->billedBy()->vatId(),
        );
        $sellerLocation = explode(' ', $invoice->billedBy()->location(), 2);
        $builder->setDocumentSellerAddress(
            lineOne: $invoice->billedBy()->street(),
            postCode: $sellerLocation[0],
            city: $sellerLocation[1],
            country: $invoice->billedBy()->country(),
        );
        $builder->setDocumentSellerCommunication(
            ZugferdElectronicAddressScheme::UNECE3155_EM,
            $invoice->billedBy()->contactInformation()->mail(),
        );

        $builder->setDocumentSellerContact(
            contactPersonName: $invoice->billedBy()->name(),
            contactDepartmentName: null,
            contactPhoneNo: $invoice->billedBy()->contactInformation()->phone(),
            contactFaxNo: null,
            contactEmailAddress: $invoice->billedBy()->contactInformation()->mail(),
        );
        $builder->setDocumentBuyer($invoice->billedTo()->name());
        if ($invoice->billedTo()->vatId() !== '') {
            $builder->addDocumentBuyerTaxRegistration(
                ZugferdReferenceCodeQualifiers::VAT_REGI_NUMB,
                $invoice->billedTo()->vatId(),
            );
        }
        $buyerLocation = explode(' ', $invoice->billedTo()->location(), 2);
        $builder->setDocumentBuyerAddress(
            lineOne: $invoice->billedTo()->street(),
            postCode: $buyerLocation[0],
            city: $buyerLocation[1],
            country: $invoice->billedTo()->country(),
        );

        $builder->addDocumentTax(
            categoryCode: ZugferdVatCategoryCodes::STAN_RATE,
            typeCode: ZugferdVatTypeCodes::VALUE_ADDED_TAX,
            basisAmount: $invoice->netAmount()->asFloat(),
            calculatedAmount: $invoice->vatAmount()->asFloat(),
            rateApplicablePercent: $invoice->taxRate()->asFloat(),
        );

        foreach ($invoice->invoiceItems() as $item) {
            $builder->addNewPosition((string) $item->position());
            $builder->setDocumentPositionProductDetails(name: $item->description());
            $seconds = $item->durationInSeconds();
            $quantity = $seconds ?? $item->quantity();
            $unit = $seconds === null ? $item->unit() : 'SEC';
            $baseQuantity = $seconds === null ? 1 : 3600;
            $builder->setDocumentPositionGrossPrice($item->pricePerItem()->asFloat(), $baseQuantity, $unit);
            $builder->setDocumentPositionNetPrice($item->pricePerItem()->asFloat(), $baseQuantity, $unit);
            $builder->setDocumentPositionQuantity($quantity, $unit);
            $builder->addDocumentPositionTax(
                ZugferdVatCategoryCodes::STAN_RATE,
                ZugferdVatTypeCodes::VALUE_ADDED_TAX,
                $invoice->taxRate()->asFloat(),
            );
            $builder->setDocumentPositionLineSummation($item->priceTotal()->asFloat());
        }

        $builder->setDocumentSummation(
            grandTotalAmount: $invoice->grossAmount()->asFloat(),
            duePayableAmount: $invoice->payableAmount()->asFloat(),
            lineTotalAmount: $invoice->netAmount()->asFloat(),
            taxBasisTotalAmount: $invoice->netAmount()->asFloat(),
            taxTotalAmount: $invoice->vatAmount()->asFloat(),
            totalPrepaidAmount: $invoice->prepaidAmount()->asFloat(),
        );

        $builder->addDocumentPaymentMean(
            typeCode: ZugferdPaymentMeans::UNTDID_4461_58,
            payeeIban: $invoice->billedBy()->paymentInformation()->iban(),
            payeeAccountName: $invoice->billedBy()->name(),
            payeeBic: $invoice->billedBy()->paymentInformation()->bic(),
        );

        $builder->addDocumentPaymentTerm(dueDate: new \DateTime($invoice->dueDate()->dueDate()->asString()));

        $validator = new ZugferdXsdValidator($builder);
        $validator->validate();
        if ($validator->hasValidationErrors()) {
            throw new \RuntimeException('Unable to build a valid E-Invoice.');
        }

        return $builder->getContent();
    }
}
