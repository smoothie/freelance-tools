<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Integration\DomPdf;

use Dompdf\Dompdf;
use Dompdf\Frame;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\UsesNamespace;
use Smoothie\FreelanceTools\Domain\Model\AdvancePayment;
use Smoothie\FreelanceTools\Domain\Model\Common\Amount;
use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;
use Smoothie\FreelanceTools\Domain\Model\InvoiceItem;
use Smoothie\FreelanceTools\Infrastructure\DomPdf\DomPdfBuilder;
use Smoothie\FreelanceTools\Infrastructure\Twig\TwigExtension;
use Smoothie\FreelanceTools\Tests\Unit\Domain\InvoiceFactoryMethods;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

#[Large]
#[Group('dompdf')]
#[CoversClass(TwigExtension::class)]
#[CoversClass(DomPdfBuilder::class)]
#[UsesNamespace('Smoothie\FreelanceTools\Domain\Model')]
class MarcInvoiceTemplateTest extends KernelTestCase
{
    use InvoiceFactoryMethods;

    public function testThatMarcCanRenderLegacyContextWithoutChangingClassic(): void
    {
        $invoice = $this->anInvoice();
        $twig = self::getContainer()->get(Environment::class);
        $html = $twig->render('pdf/marc/invoice.html.twig', $invoice->context());
        $classic = $twig->render('pdf/timesheet/invoice.html.twig', $invoice->context());

        self::assertStringContainsString('#FFCC33', $html);
        self::assertStringContainsString('poppins-latin-600-normal.ttf', $html);
        self::assertStringContainsString('poppins-latin-900-normal.ttf', $html);
        self::assertStringContainsString('mulish-latin-400-normal.ttf', $html);
        self::assertStringContainsString('Rechnung Nr.', $html);
        self::assertStringContainsString('Bake and chill cheesecakes', $html);
        self::assertStringContainsString('03.03.2025', $html);
        self::assertStringContainsString('DE11 1111 0000 1111 1111 1111', $html);
        self::assertStringNotContainsString('<img', $html);
        self::assertStringNotContainsString('Bestellnummer', $html);
        self::assertStringNotContainsString('Verrechnete Anzahlung:', $html);
        self::assertStringContainsString('Ich danke', $classic);
        self::assertStringNotContainsString('senderName', $classic);
    }

    public function testThatColumnLabelsDistinguishHourlyRatesFromOtherUnitPrices(): void
    {
        $hourly = new InvoiceItem(1, '2.5', 'Support', Amount::fromString('90.00'), 'HUR');
        $tracked = InvoiceItem::fromTrackedSeconds(1, 3607, 'Tracked support', Amount::fromString('90.00'));
        $package = new InvoiceItem(1, '2', 'Package', Amount::fromString('100.00'), 'C62');
        $twig = self::getContainer()->get(Environment::class);
        foreach (['pdf/marc/invoice.html.twig', 'pdf/timesheet/invoice.html.twig'] as $template) {
            foreach ([[$hourly], [$tracked], [$package], [$package, $hourly->withPosition(2)]] as $items) {
                $html = $twig->render($template, array_replace($this->anInvoice()->context(), ['invoiceItems' => $items]));
                $document = new \DOMDocument();
                @$document->loadHTML($html);
                $xpath = new \DOMXPath($document);
                $headings = $xpath->evaluate('string(//table[contains(@class, "positions") or contains(@class, "-invoice")]/thead)');
                self::assertIsString($headings);
                self::assertStringContainsString('Anzahl', $headings);
                self::assertStringNotContainsString('Menge', $headings);
                self::assertStringContainsString($items[0]->unit() === 'HUR' ? 'Stundensatz' : 'Einzelpreis', $headings);
                self::assertStringNotContainsString($items[0]->unit() === 'HUR' ? 'Einzelpreis' : 'Stundensatz', $headings);
            }
        }
    }

    public function testThatAdvanceAndQuoteHaveAppropriateLabelsAndTerms(): void
    {
        $context = $this->anInvoice()->context();
        $twig = self::getContainer()->get(Environment::class);
        $advance = $twig->render('pdf/marc/invoice.html.twig', array_replace($context, [
            'documentType' => 'advance',
            'orderNumber' => 'PO-42',
            'reference' => 'Website <Projekt>',
            'documentNotes' => "Erste Zeile\nZweite Zeile <script>alert(1)</script>",
        ]));
        $quote = $twig->render('pdf/marc/invoice.html.twig', array_replace($context, ['documentType' => 'quote']));

        self::assertStringContainsString('Anzahlungsrechnung Nr.', $advance);
        self::assertStringContainsString('Voraussichtlicher Leistungszeitraum', $advance);
        self::assertStringContainsString('Bestellnummer', $advance);
        self::assertStringContainsString('PO-42', $advance);
        self::assertStringContainsString('Website &lt;Projekt&gt;', $advance);
        self::assertStringContainsString('Erste Zeile<br', $advance);
        self::assertStringContainsString('&lt;script&gt;', $advance);
        self::assertStringNotContainsString('<script>', $advance);
        self::assertStringContainsString('Angebot Nr.', $quote);
        self::assertStringNotContainsString('Zahlung bis', $quote);
    }

    public function testThatFinalInvoiceListsReceivedPaymentsAndRemainingAmount(): void
    {
        $html = self::getContainer()->get(Environment::class)->render('pdf/marc/invoice.html.twig', array_replace($this->anInvoice()->context(), [
            'documentType' => 'final',
            'prepaidAmount' => Amount::fromFloat(416.50),
            'payableAmount' => Amount::fromFloat(416.50),
            'payments' => [new AdvancePayment(
                invoiceNumber: 'ADV-2025-001',
                invoiceDate: DateTime::fromDateString('2025-01-01'),
                receivedAt: DateTime::fromDateString('2025-01-10'),
                netAmount: Amount::fromFloat(350),
                vatAmount: Amount::fromFloat(66.50),
                grossAmount: Amount::fromFloat(416.50),
            )],
        ]));

        self::assertStringContainsString('Schlussrechnung Nr.', $html);
        self::assertStringContainsString('Verrechnete Anzahlung:', $html);
        self::assertStringContainsString('ADV-2025-001 vom 01.01.2025', $html);
        self::assertStringContainsString('Zahlungseingang: 10.01.2025', $html);
        self::assertStringContainsString('350,00', $html);
        self::assertStringContainsString('66,50', $html);
        self::assertStringContainsString('Abzüglich erhaltener Anzahlung', $html);
        self::assertStringContainsString('Verbleibender Zahlbetrag</th>', $html);
        self::assertStringContainsString('<td class="money">416,50</td>', $html);
    }

    public function testThatAShortMarcInvoiceGeneratesOnePageWithEmbeddedFonts(): void
    {
        $html = self::getContainer()->get(Environment::class)->render('pdf/marc/invoice.html.twig', $this->anInvoice()->context());
        $builder = self::getContainer()->get(DomPdfBuilder::class)->initialize('Marc invoice');

        self::assertSame(1, $builder->getPageNumber($html));
        $pdf = $builder->initialize('Marc invoice')->load($html)->build();

        self::assertStringStartsWith('%PDF-', $pdf);
        self::assertTrue(str_contains($pdf, 'PoppinsBlack'), 'The reference Poppins Black font must be embedded.');
        self::assertStringContainsString('PoppinsSemiBold', $pdf);
        self::assertTrue(str_contains($pdf, '+Mulish'), 'The reference Mulish font must be embedded.');
    }

    public function testThatLetterZonesHaveMeasuredFormBOffsets(): void
    {
        $html = self::getContainer()->get(Environment::class)->render('pdf/marc/invoice.html.twig', $this->anInvoice()->context());
        $options = self::getContainer()->get(DomPdfBuilder::class)->newOptions();
        $pdf = new Dompdf($options);
        $boxes = [];
        $pdf->setCallbacks([['event' => 'end_frame', 'f' => static function (Frame $frame) use (&$boxes): void {
            $node = $frame->get_node();
            if ($node instanceof \DOMElement) {
                $boxes[$node->getAttribute('class')] = $frame->get_border_box();
            }
        }]]);
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('A4');
        $pdf->render();
        $pointsPerMillimetre = 72 / 25.4;

        self::assertEqualsWithDelta(25, $boxes['pageHeader'][0] / $pointsPerMillimetre, 0.1);
        self::assertEqualsWithDelta(14, $boxes['pageHeader'][1] / $pointsPerMillimetre, 0.1);
        self::assertEqualsWithDelta(165, $boxes['pageHeader'][2] / $pointsPerMillimetre, 0.1);
        self::assertEqualsWithDelta(45, $boxes['returnAddress'][1] / $pointsPerMillimetre, 0.1);
        self::assertEqualsWithDelta(25, $boxes['recipient'][0] / $pointsPerMillimetre, 0.1);
        self::assertEqualsWithDelta(62.7, $boxes['recipient'][1] / $pointsPerMillimetre, 0.1);
        self::assertLessThanOrEqual(90, ($boxes['recipient'][1] + $boxes['recipient'][3]) / $pointsPerMillimetre);
        self::assertEqualsWithDelta(125, $boxes['infobox'][0] / $pointsPerMillimetre, 0.1);
        self::assertEqualsWithDelta(49, $boxes['infobox'][1] / $pointsPerMillimetre, 0.1);
        self::assertStringContainsString('font-size: 12pt; line-height: 1.2', $html);
        self::assertStringContainsString('font-size: 9.5pt; line-height: 1.1', $html);
        self::assertStringContainsString('border: 0.5pt solid #D9D9D9', $html);
    }

    public function testThatLongInvoicesGenerateMultiplePagesWithRepeatedTableHeadingsAndFooter(): void
    {
        $invoice = $this->anInvoice();
        for ($position = 2; $position <= 65; ++$position) {
            $invoice->addInvoiceItem(new InvoiceItem(
                position: $position,
                quantity: 1,
                description: 'Position '.$position.': '.str_repeat('Technische Unterstützung für das Website-Projekt. ', 3),
                pricePerItem: Amount::fromFloat(90),
            ));
        }
        $twig = self::getContainer()->get(Environment::class);
        $builder = self::getContainer()->get(DomPdfBuilder::class)->initialize('Long Marc invoice');
        $context = $invoice->context();
        $pageCount = $builder->getPageNumber($twig->render('pdf/marc/invoice.html.twig', $context));
        $context['lastPageNumber'] = $pageCount;
        $html = $twig->render('pdf/marc/invoice.html.twig', $context);

        self::assertGreaterThan(2, $pageCount);
        self::assertLessThan(15, $pageCount);
        self::assertSame($pageCount, $builder->initialize('Long Marc invoice')->getPageNumber($html));
        self::assertStringContainsString('Position 65:', $html);
        self::assertStringContainsString('display: table-header-group', $html);
        self::assertStringContainsString('/ '.$pageCount, $html);
        self::assertStringContainsString('class="pageFooter"', $html);
        $pdf = $builder->initialize('Long Marc invoice')->load($html)->build();
        self::assertStringStartsWith('%PDF-', $pdf);
        self::assertSame($pageCount, preg_match_all('~/Type\s*/Page\b~', $pdf));
        $pages = $this->pageStreams($pdf);
        self::assertCount($pageCount, $pages);
        foreach ($pages as $page) {
            self::assertStringContainsString(mb_convert_encoding('Anotherstreet 4', 'UTF-16BE'), $page);
            self::assertStringContainsString(mb_convert_encoding('Leistung', 'UTF-16BE'), $page);
        }
        self::assertStringContainsString(mb_convert_encoding('Position 65:', 'UTF-16BE'), implode('', $pages));
    }

    /**
     * @return list<string>
     */
    private function pageStreams(string $pdf): array
    {
        preg_match_all('~/Type\s*/Page\b.*?/Contents\s+(\d+)\s+0\s+R~s', $pdf, $matches);
        $pages = [];
        foreach ($matches[1] as $objectId) {
            self::assertSame(1, preg_match('~\n'.$objectId.' 0 obj\b.*?stream\r?\n(.*?)\r?\nendstream~s', $pdf, $stream));
            $decoded = gzuncompress($stream[1]);
            self::assertIsString($decoded);
            $pages[] = $decoded;
        }

        return $pages;
    }
}
