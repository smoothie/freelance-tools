<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Integration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\UsesNamespace;
use Smoothie\FreelanceTools\Application\Application;
use Smoothie\FreelanceTools\Application\ApplicationInterface;
use Smoothie\FreelanceTools\Application\BillingDocuments;
use Smoothie\FreelanceTools\Application\CreateBillingDocument;
use Smoothie\FreelanceTools\Application\FilesystemInterface;
use Smoothie\FreelanceTools\Domain\Model\FilterCriteria;
use Smoothie\FreelanceTools\Domain\Model\Task;
use Smoothie\FreelanceTools\Domain\Model\Timing;
use Smoothie\FreelanceTools\Domain\Service\WorkTimeProcessor;
use Smoothie\FreelanceTools\Infrastructure\Symfony\BillingDocumentInput;
use Smoothie\FreelanceTools\Infrastructure\Symfony\Command\GenerateBillingDocumentCommand;
use Smoothie\FreelanceTools\Infrastructure\SystemClock;
use Smoothie\FreelanceTools\Tests\Doubles\RecordingFilesystem;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;

#[Large]
#[CoversClass(Application::class)]
#[CoversClass(BillingDocuments::class)]
#[CoversClass(CreateBillingDocument::class)]
#[CoversClass(BillingDocumentInput::class)]
#[CoversClass(GenerateBillingDocumentCommand::class)]
#[UsesNamespace('Smoothie\\FreelanceTools\\Domain')]
#[UsesNamespace('Smoothie\\FreelanceTools\\Infrastructure')]
#[UsesNamespace('Smoothie\\FreelanceTools\\Tests')]
final class BillingDocumentWorkflowTest extends KernelTestCase
{
    #[DataProvider('documentTypes')]
    public function testExamplesGenerateActualDocumentsThroughTheCommand(string $sample, string $type, string $template): void
    {
        $container = self::getContainer();
        $filesystem = new RecordingFilesystem();
        $container->set(FilesystemInterface::class, $filesystem);
        $processor = $this->createMock(WorkTimeProcessor::class);
        if ($sample === 'projects') {
            $processor->expects(self::exactly(4))->method('getListOfTasks')->willReturnCallback(static function (FilterCriteria $filter): array {
                $project = str_contains($filter->getQuery(), 'project = \'website\'') ? 'website' : 'maintenance';

                return [new Task($project, 'example-customer', 'Dokumentierte Projektarbeit', [], [new Timing('2026-01-12 09:00:00', $project === 'website' ? '2026-01-12 10:00:07' : '2026-01-12 11:30:00')])];
            });
        } else {
            $processor->expects(self::never())->method('getListOfTasks');
        }
        $organization = ['project' => 'website', 'customerId' => 'example-customer', 'name' => 'Beispielkunde GmbH', 'street' => 'Beispielstraße 2', 'location' => '10115 Berlin', 'country' => 'DE', 'vatId' => 'DE999999998', 'description' => 'Firmenwebsite', 'taxRate' => 19.0, 'pricePerHour' => 90.0, 'termOfPaymentInDays' => 14];
        $service = new BillingDocuments(
            $container->get(ApplicationInterface::class),
            new SystemClock(),
            $processor,
            [$organization, array_replace($organization, ['project' => 'maintenance', 'description' => 'Technische Betreuung', 'pricePerHour' => 85.0])],
            ['name' => 'Beispiel Freiberufler', 'street' => 'Musterstraße 1', 'location' => '69118 Heidelberg', 'country' => 'DE', 'vatId' => 'DE123456788', 'phone' => '+496221123456', 'mail' => 'kontakt@example.com', 'web' => 'https://example.com', 'bank' => 'Beispielbank', 'iban' => 'DE89370400440532013000', 'bic' => 'COBADEFFXXX'],
        );
        $parser = new BillingDocumentInput();
        $data = $this->sampleInput($sample);
        $data['templateId'] = $template;
        $expected = $service->create($parser->parse($data));
        $directory = \dirname(__DIR__, 2).'/var/billing-examples';
        if (! is_dir($directory)) {
            self::assertTrue(mkdir($directory, 0o777, true));
        }
        $prefix = $directory.'/'.$sample.'-'.$template;
        $this->save($prefix.'.yaml', Yaml::dump($data, 5, 2));
        $tester = new CommandTester(new GenerateBillingDocumentCommand($service, $parser));

        self::assertSame(Command::SUCCESS, $tester->execute(['--input' => $prefix.'.yaml'], ['interactive' => false]));
        self::assertStringContainsString($expected->invoiceId()->asString(), $tester->getDisplay());
        $pdf = $filesystem->latestContents($expected->fileName('pdf'));
        self::assertStringStartsWith('%PDF-', $pdf);
        self::assertGreaterThan(1000, \strlen($pdf));
        $this->save($prefix.'.pdf', $pdf);
        self::assertSame($type !== 'quote', $filesystem->wasFileDumped($expected->fileName('xml')));
        if ($type !== 'quote') {
            self::assertNotSame($filesystem->getFileContents($expected->fileName('pdf')), $pdf);
            self::assertStringContainsString('/EmbeddedFiles', $pdf);
            $content = $filesystem->latestContents($expected->fileName('xml'));
            $this->save($prefix.'.xml', $content);
            $xml = new \DOMDocument();
            self::assertTrue($xml->loadXML($content));
            $xpath = new \DOMXPath($xml);
            $xpath->registerNamespace('ram', 'urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100');
            self::assertSame($expected->payableAmount()->asDecimal(), $xpath->evaluate('string(//ram:DuePayableAmount)'));
        }
        $payable = match ($sample) {
            'final' => '891.90',
            'final-multiple' => '772.90',
            'final-partial' => '1426.81',
            'final-paid' => '0.00',
            'projects' => '360.19',
            'mixed' => '2170.56',
            default => $expected->payableAmount()->asDecimal(),
        };
        self::assertSame($payable, $expected->payableAmount()->asDecimal());
        $pageCount = preg_match_all('~/Type\s*/Page\b~', $pdf);
        if ($sample === 'multipage') {
            self::assertGreaterThan(1, $pageCount);
            self::assertLessThanOrEqual(6, $pageCount);
        } elseif ($type === 'quote') {
            self::assertLessThanOrEqual(2, $pageCount);
        } else {
            self::assertSame(1, $pageCount, $sample.' '.$template.' must fit on one page.');
        }
        $html = $container->get(Environment::class)->render($expected->template(), $expected->context());
        self::assertStringContainsString('<main', $html);
        self::assertStringContainsString('scope="col"', $html);
        $this->save($prefix.'.html', $html);
        if ($sample === 'final-paid') {
            self::assertStringContainsString('Die Rechnung ist vollständig bezahlt.', $html);
            self::assertStringNotContainsString('Zahlung bis', $html);
            self::assertStringNotContainsString('Zahlung innerhalb', $html);
        }
        $this->save($directory.'/README.md', self::exampleIndex());
    }

    /**
     * @return array<string, mixed>
     */
    private function sampleInput(string $sample): array
    {
        $source = match ($sample) {
            'final-multiple', 'final-partial', 'final-paid' => 'final',
            'mixed', 'projects', 'multipage' => 'invoice',
            default => $sample,
        };
        $data = Yaml::parseFile(\dirname(__DIR__, 2).'/docs/examples/billing-'.$source.'.yaml');
        self::assertIsArray($data);
        if ($sample !== $source) {
            $data['number'] = 'DEMO-'.strtoupper($sample).'-2026-001';
        }
        if ($sample === 'mixed') {
            $data['items'][] = ['description' => 'Zusätzliche Standardseite', 'quantity' => '1', 'unit' => 'C62', 'pricePerItem' => '100.00'];
            $data['items'][] = ['description' => 'Technische Unterstützung', 'quantity' => '2.5', 'unit' => 'HUR', 'pricePerItem' => '90.00'];
        }
        if ($sample === 'projects') {
            $data['items'] = [];
            $data['projects'] = ['website', 'maintenance'];
        }
        if ($sample === 'final-multiple') {
            $data['documentNotes'] = 'Vollständige Website-Leistung abzüglich zweier tatsächlich erhaltener Anzahlungen.';
            $data['advancePayments'][] = ['invoiceNumber' => 'ADV-2026-002', 'invoiceDate' => '2026-01-16', 'receivedAt' => '2026-01-20', 'netAmount' => '100.00', 'vatAmount' => '19.00', 'grossAmount' => '119.00'];
        }
        if ($sample === 'final-partial' || $sample === 'final-paid') {
            $data['documentNotes'] = $sample === 'final-paid' ? 'Die vollständige Vergütung wurde bereits als Anzahlung vereinnahmt. Kein weiterer Zahlbetrag.' : 'Auf die ausgestellte Anzahlungsrechnung wurden nur 300,00 EUR netto zuzüglich 57,00 EUR Umsatzsteuer bezahlt. Nur dieser Zahlungseingang wird verrechnet.';
            if ($sample === 'final-paid') {
                $data['reference'] = 'ADV-FULL-2026-001';
                $data['advancePayments'][0]['invoiceNumber'] = 'ADV-FULL-2026-001';
            }
            $data['advancePayments'][0]['netAmount'] = $sample === 'final-paid' ? '1499.00' : '300.00';
            $data['advancePayments'][0]['vatAmount'] = $sample === 'final-paid' ? '284.81' : '57.00';
            $data['advancePayments'][0]['grossAmount'] = $sample === 'final-paid' ? '1783.81' : '357.00';
        }
        if ($sample === 'multipage') {
            $data['items'] = [];
            for ($position = 1; $position <= 35; ++$position) {
                $data['items'][] = ['description' => 'Arbeitspaket '.$position.': Konzeption, Umsetzung und dokumentierte Prüfung der abgestimmten Website-Funktionen.', 'quantity' => '1.25', 'unit' => 'HUR', 'pricePerItem' => '90.00'];
            }
        }

        return $data;
    }

    private function save(string $path, string $content): void
    {
        self::assertSame(\strlen($content), file_put_contents($path, $content));
    }

    private static function exampleIndex(): string
    {
        $index = "# Gerenderte Abrechnungsbeispiele\n\nNur fiktive Testdaten, keine versandfertigen Dokumente. Jeder Fall ist ein eigenständiges Szenario; classic und marc zeigen dieselben Daten.\n\nNeu erzeugen: `./tools/phpunit tests/Integration/BillingDocumentWorkflowTest.php`\n\nBeide Layouts je Fall; Rechnungs-PDFs enthalten das XML. YAML dokumentiert die Eingabe. Projektzeiten stammen aus dem Testdouble, nicht aus Toggl.\n\n| Fall | Layout | PDF | Eingabe | XML | HTML-Quelle |\n| --- | --- | --- | --- | --- | --- |\n";
        foreach (self::documentTypes() as [$sample, $type, $template]) {
            $prefix = $sample.'-'.$template;
            $index .= '| '.$sample.' | '.$template.' | [PDF]('.$prefix.'.pdf) | [YAML]('.$prefix.'.yaml) | '.($type === 'quote' ? '–' : '[XML]('.$prefix.'.xml)').' | [HTML]('.$prefix.'.html)'." |\n";
        }

        return $index;
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function documentTypes(): iterable
    {
        foreach (['classic', 'marc'] as $template) {
            foreach (['invoice' => 'invoice', 'mixed' => 'invoice', 'projects' => 'invoice', 'advance' => 'advance', 'final' => 'final', 'final-multiple' => 'final', 'final-partial' => 'final', 'final-paid' => 'final', 'quote' => 'quote', 'quote-hours' => 'quote', 'multipage' => 'invoice'] as $sample => $type) {
                yield $sample.' '.$template => [$sample, $type, $template];
            }
        }
    }
}
