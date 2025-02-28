<?php

declare(strict_types=1);

namespace App\Infrastructure\horstoeko;

use App\Domain\Model\Invoice;
use horstoeko\zugferd\codelists\ZugferdCurrencyCodes;
use horstoeko\zugferd\codelists\ZugferdDocumentType;
use horstoeko\zugferd\codelists\ZugferdElectronicAddressScheme;
use horstoeko\zugferd\codelists\ZugferdPaymentMeans;
use horstoeko\zugferd\codelists\ZugferdReferenceCodeQualifiers;
use horstoeko\zugferd\codelists\ZugferdVatCategoryCodes;
use horstoeko\zugferd\codelists\ZugferdVatTypeCodes;
use horstoeko\zugferd\ZugferdDocumentBuilder;
use horstoeko\zugferd\ZugferdProfiles;
use horstoeko\zugferd\ZugferdXsdValidator;

class EInvoiceBuilder
{
    public function build(Invoice $invoice): string
    {
        $builder = ZugferdDocumentBuilder::createNew(ZugferdProfiles::PROFILE_EN16931);
        $builder->setDocumentBusinessProcess('Invoice');
        $builder->setDocumentInformation(
            documentNo: $invoice->invoiceId()->asString(),
            documentTypeCode: ZugferdDocumentType::COMMERCIAL_INVOICE,
            documentDate: new \DateTime($invoice->billingDate()->asString()),
            invoiceCurrency: ZugferdCurrencyCodes::EURO,
            documentName: $invoice->title(),
            documentLanguage: 'de_DE',
            effectiveSpecifiedPeriod: new \DateTime($invoice->dueDate()->dueDate()->asString()),
        );
        $builder->setDocumentSeller($invoice->billedBy()->name());
        $builder->addDocumentSellerTaxRegistration(
            ZugferdReferenceCodeQualifiers::VAT_REGI_NUMB,
            $invoice->billedBy()->vatId(),
        );
        $sellerLocation = explode(' ', $invoice->billedBy()->location());
        $builder->setDocumentSellerAddress(
            lineOne: $invoice->billedBy()->street(),
            postCode: $sellerLocation[0],
            city: $sellerLocation[1],
            country: 'DE',
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
        $builder->setDocumentBuyerReference($invoice->invoiceId()->asString());
        $buyerLocation = explode(' ', $invoice->billedBy()->location());
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
            $builder->setDocumentPositionGrossPrice($item->pricePerItem()->asFloat());
            $builder->setDocumentPositionNetPrice($item->pricePerItem()->asFloat());
            $builder->setDocumentPositionQuantity($item->quantity(), $item->unit());
            $builder->addDocumentPositionTax(
                ZugferdVatCategoryCodes::STAN_RATE,
                ZugferdVatTypeCodes::VALUE_ADDED_TAX,
                $invoice->taxRate()->asFloat(),
            );
            $builder->setDocumentPositionLineSummation($item->priceTotal()->asFloat());
        }

        $builder->setDocumentSummation(
            grandTotalAmount: $invoice->grossAmount()->asFloat(),
            duePayableAmount: $invoice->grossAmount()->asFloat(),
            lineTotalAmount: $invoice->netAmount()->asFloat(),
            taxBasisTotalAmount: $invoice->netAmount()->asFloat(),
            taxTotalAmount: $invoice->vatAmount()->asFloat(),
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
