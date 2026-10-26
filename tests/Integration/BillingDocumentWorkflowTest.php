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
use Smoothie\FreelanceTools\Domain\Service\WorkTimeProcessor;
use Smoothie\FreelanceTools\Infrastructure\Symfony\BillingDocumentInput;
use Smoothie\FreelanceTools\Infrastructure\Symfony\Command\GenerateBillingDocumentCommand;
use Smoothie\FreelanceTools\Infrastructure\SystemClock;
use Smoothie\FreelanceTools\Tests\Acceptance\InMemoryFilesystem;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Yaml\Yaml;

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
    public function testExamplesGenerateActualDocumentsThroughTheCommand(string $sample, string $type): void
    {
        $container = self::getContainer();
        $processor = $this->createMock(WorkTimeProcessor::class);
        $processor->expects(self::never())->method('getListOfTasks');
        $service = new BillingDocuments(
            $container->get(ApplicationInterface::class),
            new SystemClock(),
            $processor,
            [['project' => 'website', 'customerId' => 'example-customer', 'name' => 'Example Buyer GmbH', 'street' => 'Buyer Street 2', 'location' => '10115 Berlin', 'country' => 'DE', 'vatId' => 'DE999999998', 'description' => 'Website project', 'taxRate' => 19.0, 'pricePerHour' => 90.0, 'termOfPaymentInDays' => 14]],
            ['name' => 'Example Seller GmbH', 'street' => 'Seller Street 1', 'location' => '10115 Berlin', 'country' => 'DE', 'vatId' => 'DE123456788', 'phone' => '+493012345678', 'mail' => 'seller@example.com', 'web' => 'https://example.com', 'bank' => 'Example Bank', 'iban' => 'DE89370400440532013000', 'bic' => 'COBADEFFXXX'],
        );
        $parser = new BillingDocumentInput();
        $path = \dirname(__DIR__, 2).'/docs/examples/billing-'.$sample.'.yaml';
        $expected = $service->create($parser->parse(Yaml::parseFile($path)));
        $tester = new CommandTester(new GenerateBillingDocumentCommand($service, $parser));

        self::assertSame(Command::SUCCESS, $tester->execute(['--input' => $path], ['interactive' => false]));
        self::assertStringContainsString($expected->invoiceId()->asString(), $tester->getDisplay());
        $filesystem = $container->get(FilesystemInterface::class);
        self::assertInstanceOf(InMemoryFilesystem::class, $filesystem);
        self::assertTrue($filesystem->wasFileDumped($expected->fileName('pdf')));
        self::assertStringStartsWith('%PDF-', $filesystem->getFileContents($expected->fileName('pdf')));
        self::assertSame($type !== 'quote', $filesystem->wasFileDumped($expected->fileName('xml')));
        if ($type !== 'quote') {
            $xml = new \DOMDocument();
            self::assertTrue($xml->loadXML($filesystem->getFileContents($expected->fileName('xml'))));
            $xpath = new \DOMXPath($xml);
            $xpath->registerNamespace('ram', 'urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100');
            self::assertSame($expected->payableAmount()->asDecimal(), $xpath->evaluate('string(//ram:DuePayableAmount)'));
        }
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function documentTypes(): iterable
    {
        foreach (['invoice', 'advance', 'final', 'quote'] as $type) {
            yield $type => [$type, $type];
        }
        yield 'hour budget quote' => ['quote-hours', 'quote'];
    }
}
