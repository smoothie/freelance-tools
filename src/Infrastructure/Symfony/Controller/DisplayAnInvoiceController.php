<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Infrastructure\Symfony\Controller;

use horstoeko\zugferd\ZugferdDocumentPdfMerger;
use Smoothie\FreelanceTools\Domain\Model\BilledBy;
use Smoothie\FreelanceTools\Domain\Model\BilledTo;
use Smoothie\FreelanceTools\Domain\Model\Common\Amount;
use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;
use Smoothie\FreelanceTools\Domain\Model\ContactInformation;
use Smoothie\FreelanceTools\Domain\Model\DueDate;
use Smoothie\FreelanceTools\Domain\Model\Invoice;
use Smoothie\FreelanceTools\Domain\Model\InvoiceId;
use Smoothie\FreelanceTools\Domain\Model\InvoiceItem;
use Smoothie\FreelanceTools\Domain\Model\PaymentInformation;
use Smoothie\FreelanceTools\Domain\Model\ProjectId;
use Smoothie\FreelanceTools\Infrastructure\DomPdf\DomPdfBuilder;
use Smoothie\FreelanceTools\Infrastructure\horstoeko\EInvoiceBuilder;
use Smoothie\FreelanceTools\Infrastructure\Symfony\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[When('dev')]
final class DisplayAnInvoiceController extends AbstractController
{
    public function __construct(
        private DomPdfBuilder $domPdfBuilder,
        private Filesystem $filesystem,
        private Environment $twig,
        private EInvoiceBuilder $invoiceBuilder,
    ) {
    }

    #[Route('/invoice', name: 'tools.display_invoice')]
    public function __invoke(): Response
    {
        $invoice = $this->anInvoice();
        $context = $invoice->context();
        $html = $this->twig->render('pdf/timesheet/invoice.html.twig', $context);

        $xml = $this->invoiceBuilder->build($invoice);
        $pdf = $this->domPdfBuilder->initialize('some')
            ->load($html)
            ->build();

        $merger = new ZugferdDocumentPdfMerger($xml, $pdf);
        $merger->generateDocument();

        $this->filesystem->dumpExport(file: 'trying/invoice.pdf', content: $merger->downloadString());
        $this->filesystem->dumpExport(file: 'trying/invoice.xml', content: $xml);

        return new Response($pdf, Response::HTTP_OK, ['Content-Type' => 'application/pdf']);
    }

    private function anInvoice(): Invoice
    {
        $invoice = new Invoice(
            title: 'Invoice Number 202502-02',
            projectId: ProjectId::fromString('cheesecake-agile'),
            invoiceId: InvoiceId::fromString('Invoice Number 202502-02'),
            description: 'Bake and chill cheesecakes',
            taxRate: Amount::fromFloat(19.0),
            billingDate: DateTime::fromDateString('2025-02-03'),
            dueDate: new DueDate(
                dueDate: DateTime::fromDateString('2025-03-03'),
                termOfPaymentInDays: 30,
            ),
            billedBy: new BilledBy(
                name: 'Marc Eichenseher',
                street: 'Anotherstreet 4',
                location: '55555 Woot',
                vatId: 'DE000000001',
                country: 'DE',
                contactInformation: new ContactInformation(
                    phone: '+4900012345678',
                    mail: 'some@example.com',
                    web: 'marceichenseher.de',
                ),
                paymentInformation: new PaymentInformation(
                    bank: 'A Bank',
                    iban: 'DE11 1111 0000 1111 1111 1111',
                    bic: 'AABICYA1111',
                ),
            ),
            billedTo: new BilledTo(
                name: 'BilledTo GmbH',
                street: 'Somestreet 1',
                location: '55555 Some',
                vatId: 'DE900000009',
                country: 'DE',
            ),
            deliveredAt: DateTime::fromDateString('2025-01-31'),
            lastPageNumber: 1,
        );

        $invoice->addInvoiceItem(
            new InvoiceItem(
                position: 1,
                quantity: 119,
                description: 'Bake and chill cheesecakes',
                pricePerItem: Amount::fromFloat(70.0),
            ),
        );

        return $invoice;
    }
}
