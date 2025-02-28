<?php

declare(strict_types=1);

namespace App\Tests\Acceptance;

use App\Application\ApplicationInterface;
use App\Application\GenerateInvoice;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use Webmozart\Assert\Assert;

final class InvoiceContext extends FeatureContext
{
    private ?GenerateInvoice $command = null;

    #[Given('we have prepared a command to generate an invoice')]
    public function weHavePreparedACommand(): void
    {
        $command = new GenerateInvoice(
            invoiceId: 'an-invoice-number',
            project: 'cheesecake-agile',
            description: 'building cheesy cakey thingy',
            durationInSeconds: 119 * 60 * 60,
            deliveredAt: '2025-01-03',
            taxRate: 19.00,
            billingDate: '2025-02-03',
            pricePerHour: 133.70,
            termOfPaymentInDays: 0,
            billedBy: $this->defaultProvidedBy(),
            billedTo: $this->anOrganization(),
        );

        $this->command = $command;
    }

    #[When('we try to generate an invoice')]
    public function weGenerateAnInvoice(): void
    {
        $this->application()->generateInvoice($this->command);
    }

    #[Then('the invoice should have been rendered')]
    public function anInvoiceHasBeenRendered(): void
    {
        $filesystem = $this->serviceContainer()->filesystem();
        $today = (new \DateTimeImmutable())->format('Y-m-d');
        Assert::true($filesystem->wasFileDumped($today.' - Cheese Squad - Rechnung Nr 202502-01 - Marc Eichenseher.pdf'), 'We have assumed that we rendered exactly one pdf');
        Assert::true($filesystem->wasFileDumped($today.' - Cheese Squad - Rechnung Nr 202502-01 - Marc Eichenseher.xml'), 'We have assumed that we rendered exactly one xml');
        $filesystem->clear();
    }

    #[Then('the invoice should have been generated')]
    public function anInvoiceHasBeenGenerated(): void
    {
        $this->eventHasBeenDispatched(ApplicationInterface::EVENT_GENERATED_INVOICE);
    }
}
