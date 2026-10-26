<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Integration\DomPdf;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\UsesNamespace;
use Smoothie\FreelanceTools\Infrastructure\DomPdf\DomPdfBuilder;
use Smoothie\FreelanceTools\Tests\Unit\Domain\InvoiceFactoryMethods;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

#[Large]
#[CoversClass(DomPdfBuilder::class)]
#[UsesNamespace('Smoothie\\FreelanceTools\\Domain\\Model')]
#[UsesNamespace('Smoothie\\FreelanceTools\\Infrastructure\\Twig')]
final class ClassicClosingTemplateTest extends KernelTestCase
{
    use InvoiceFactoryMethods;

    #[DataProvider('closingBoundaries')]
    public function testGreetingsStayWithPrecedingContent(string $kind, int $lines): void
    {
        $container = self::getContainer();
        $html = $container->get(Environment::class)->render('pdf/timesheet/invoice.html.twig', array_replace($this->anInvoice()->context(), [
            'documentType' => $kind,
            'documentNotes' => str_repeat("Abgestimmte Leistung und dokumentierte Prüfung.\n", $lines).'Abschlussinhalt',
        ]));
        $pdf = $container->get(DomPdfBuilder::class)->initialize('Closing boundary')->load($html)->build();
        preg_match_all('~/Type\s*/Page\b.*?/Contents\s+(\d+)\s+0\s+R~s', $pdf, $matches);
        self::assertNotEmpty($matches[1]);
        $foundGreetings = false;
        foreach ($matches[1] as $objectId) {
            self::assertSame(1, preg_match('~\n'.$objectId.' 0 obj\b(.*?)stream\r?\n~s', $pdf, $stream, \PREG_OFFSET_CAPTURE));
            self::assertSame(1, preg_match('~/Length\s+(\d+)~', $stream[1][0], $length));
            $offset = $stream[0][1] + \strlen($stream[0][0]);
            $content = gzuncompress(substr($pdf, $offset, (int) $length[1]));
            self::assertIsString($content);
            if (str_contains($content, mb_convert_encoding('Herzliche', 'UTF-16BE'))) {
                $foundGreetings = true;
                self::assertTrue(str_contains($content, mb_convert_encoding($kind === 'quote' ? 'Abschlussinhalt' : 'Zahlungsziel', 'UTF-16BE')), 'Greetings must share their page with preceding document content.');
            }
        }
        self::assertTrue($foundGreetings, 'The greeting must be retained.');
        if ($lines === 0) {
            self::assertCount(1, $matches[1]);
        }
    }

    /**
     * @return iterable<string, array{string, int}>
     */
    public static function closingBoundaries(): iterable
    {
        foreach (['invoice', 'advance', 'quote'] as $kind) {
            foreach (range(0, 32) as $lines) {
                yield $kind.' '.$lines => [$kind, $lines];
            }
        }
    }
}
