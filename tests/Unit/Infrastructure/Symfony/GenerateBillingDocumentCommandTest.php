<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Unit\Infrastructure\Symfony;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Smoothie\FreelanceTools\Application\BillingDocuments;
use Smoothie\FreelanceTools\Application\CreateBillingDocument;
use Smoothie\FreelanceTools\Domain\Model\Invoice;
use Smoothie\FreelanceTools\Domain\Model\InvoiceId;
use Smoothie\FreelanceTools\Infrastructure\Symfony\BillingDocumentInput;
use Smoothie\FreelanceTools\Infrastructure\Symfony\Command\GenerateBillingDocumentCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Yaml\Yaml;

#[Small]
#[CoversClass(GenerateBillingDocumentCommand::class)]
final class GenerateBillingDocumentCommandTest extends TestCase
{
    #[DataProvider('formats')]
    public function testFilesAndStandardInputGenerateDocuments(string $format, bool $stdin): void
    {
        $service = $this->createMock(BillingDocuments::class);
        $invoice = $this->createMock(Invoice::class);
        $invoice->method('invoiceId')->willReturn(InvoiceId::fromString('OVERRIDE-001'));
        $invoice->method('fileName')->with('pdf')->willReturn('generated-document.pdf');
        $service->expects(self::once())->method('generate')->with(self::callback(static function (CreateBillingDocument $request): bool {
            return $request->number === 'OVERRIDE-001' && $request->documentType === 'quote' && $request->templateId === 'marc' && $request->customerId === 'example-customer';
        }))->willReturn($invoice);
        $tester = new CommandTester(new GenerateBillingDocumentCommand($service, new BillingDocumentInput()));
        $content = $format === 'json' ? json_encode(self::input(), \JSON_THROW_ON_ERROR) : Yaml::dump(self::input(), 4);
        $path = sys_get_temp_dir().'/billing-'.bin2hex(random_bytes(8)).'.'.$format;

        try {
            if ($stdin) {
                $tester->setInputs([$content]);
            } else {
                file_put_contents($path, $content);
            }
            $status = $tester->execute(['--input' => $stdin ? '-' : $path, '--number' => 'OVERRIDE-001', '--type' => 'quote', '--template' => 'marc'], ['interactive' => false]);
            self::assertSame(Command::SUCCESS, $status);
            self::assertStringContainsString('OVERRIDE-001', $tester->getDisplay());
            self::assertStringContainsString('generated-document.pdf', $tester->getDisplay());
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function formats(): iterable
    {
        yield 'JSON file' => ['json', false];
        yield 'YAML file' => ['yaml', false];
        yield 'JSON stdin' => ['json', true];
        yield 'YAML stdin' => ['yaml', true];
    }

    public function testMissingInputFailsWithActionableMessage(): void
    {
        $service = $this->createMock(BillingDocuments::class);
        $service->expects(self::never())->method('generate');
        $tester = new CommandTester(new GenerateBillingDocumentCommand($service, new BillingDocumentInput()));
        self::assertSame(Command::INVALID, $tester->execute([]));
        self::assertStringContainsString('--input=path.yaml', $tester->getDisplay());
    }

    #[DataProvider('invalidContent')]
    public function testInvalidStandardInputIsReported(string $content): void
    {
        $service = $this->createMock(BillingDocuments::class);
        $service->expects(self::never())->method('generate');
        $tester = new CommandTester(new GenerateBillingDocumentCommand($service, new BillingDocumentInput()));
        $tester->setInputs([$content]);
        self::assertSame(Command::INVALID, $tester->execute(['--input' => '-']));
        self::assertStringContainsString('ERROR', $tester->getDisplay());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidContent(): iterable
    {
        yield 'malformed JSON' => ['{"number":'];
        yield 'malformed YAML' => ['number: ['];
        yield 'scalar' => ['hello'];
        yield 'list' => ['[1,2]'];
        yield 'object tag' => ['number: !php/object \'O:8:"stdClass":0:{}\''];
        yield 'float money' => [json_encode(array_replace(self::input(), ['items' => [['description' => 'Website', 'quantity' => '1', 'pricePerItem' => 1499.0]]]), \JSON_THROW_ON_ERROR)];
    }

    public function testUnreadableFileIsReported(): void
    {
        $service = $this->createMock(BillingDocuments::class);
        $service->expects(self::never())->method('generate');
        $tester = new CommandTester(new GenerateBillingDocumentCommand($service, new BillingDocumentInput()));
        self::assertSame(Command::INVALID, $tester->execute(['--input' => '/nonexistent/billing-document.json']));
        self::assertStringContainsString('Cannot read input file', $tester->getDisplay());
    }

    public function testApplicationValidationIsReported(): void
    {
        $service = $this->createMock(BillingDocuments::class);
        $service->method('generate')->willThrowException(new \InvalidArgumentException('Unknown customer ID.'));
        $tester = new CommandTester(new GenerateBillingDocumentCommand($service, new BillingDocumentInput()));
        $tester->setInputs([json_encode(self::input(), \JSON_THROW_ON_ERROR)]);
        self::assertSame(Command::INVALID, $tester->execute(['--input' => '-']));
        self::assertStringContainsString('Unknown customer ID.', $tester->getDisplay());
    }

    public function testRenderingFailuresAreNotSwallowed(): void
    {
        $service = $this->createMock(BillingDocuments::class);
        $service->method('generate')->willThrowException(new \RuntimeException('Renderer failed.'));
        $tester = new CommandTester(new GenerateBillingDocumentCommand($service, new BillingDocumentInput()));
        $tester->setInputs([json_encode(self::input(), \JSON_THROW_ON_ERROR)]);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Renderer failed.');
        $tester->execute(['--input' => '-']);
    }

    public function testAllDocumentExamplesParse(): void
    {
        $parser = new BillingDocumentInput();
        foreach (['invoice', 'advance', 'final', 'quote'] as $type) {
            $data = Yaml::parseFile(\dirname(__DIR__, 4).'/docs/examples/billing-'.$type.'.yaml');
            $request = $parser->parse($data);
            self::assertSame($type, $request->documentType);
            self::assertSame('example-customer', $request->customerId);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private static function input(): array
    {
        return [
            'number' => 'INV-001',
            'customerId' => 'example-customer',
            'billingDate' => '2026-02-01',
            'deliveredAt' => ['2026-01-31'],
            'items' => [['description' => 'Website', 'quantity' => '1', 'unit' => 'C62', 'pricePerItem' => '1499.00']],
        ];
    }
}
