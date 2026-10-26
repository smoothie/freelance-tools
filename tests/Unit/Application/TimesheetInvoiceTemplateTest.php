<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Unit\Application;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;
use Smoothie\FreelanceTools\Application\Application;
use Smoothie\FreelanceTools\Application\FilesystemInterface;
use Smoothie\FreelanceTools\Application\GeneratedATimesheetReport;
use Smoothie\FreelanceTools\Application\GenerateTimesheet;
use Smoothie\FreelanceTools\Application\PdfAndXmlMerger;
use Smoothie\FreelanceTools\Domain\Model\Component;
use Smoothie\FreelanceTools\Domain\Model\Invoice;
use Smoothie\FreelanceTools\Domain\Model\Task;
use Smoothie\FreelanceTools\Domain\Model\TimesheetReport;
use Smoothie\FreelanceTools\Domain\Model\Timing;
use Smoothie\FreelanceTools\Domain\Service\ComponentRenderer;
use Smoothie\FreelanceTools\Domain\Service\WorkTimeProcessor;
use Smoothie\FreelanceTools\Infrastructure\SystemClock;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Small]
#[CoversClass(Application::class)]
#[CoversClass(GenerateTimesheet::class)]
#[CoversClass(GeneratedATimesheetReport::class)]
#[UsesNamespace('Smoothie\\FreelanceTools\\Domain')]
#[UsesNamespace('Smoothie\\FreelanceTools\\Infrastructure')]
#[UsesNamespace('Smoothie\\FreelanceTools\\Application')]
final class TimesheetInvoiceTemplateTest extends TestCase
{
    #[DataProvider('templates')]
    public function testTemplateSurvivesTimesheetEventAndOverridesCustomerDefault(?string $override, ?string $configured, string $expected): void
    {
        $sender = ['name' => 'Example Seller', 'street' => 'Example 1', 'location' => '10115 Berlin', 'vatId' => 'DE123456788', 'country' => 'DE', 'phone' => '+493012345678', 'mail' => 'seller@example.com', 'web' => 'https://example.com', 'bank' => 'Example Bank', 'iban' => 'DE89370400440532013000', 'bic' => 'COBADEFFXXX'];
        $organization = ['project' => 'CIL', 'name' => 'Example Buyer', 'street' => 'Buyer 2', 'location' => '10115 Berlin', 'country' => 'DE', 'vatId' => 'DE999999998', 'description' => 'Technical support', 'taxRate' => 19.0, 'pricePerHour' => 90.0, 'termOfPaymentInDays' => 14];
        if ($configured !== null) {
            $organization['templateId'] = $configured;
        }
        $components = [];
        $renderer = $this->createMock(ComponentRenderer::class);
        $renderer->expects(self::exactly(2))->method('render')->willReturnCallback(static function (Component $component) use (&$components): string {
            $components[] = $component;

            return 'pdf';
        });
        $xmlRenderer = $this->createMock(ComponentRenderer::class);
        $xmlRenderer->expects(self::once())->method('render')->willReturn('xml');
        $processor = $this->createMock(WorkTimeProcessor::class);
        $processor->expects(self::once())->method('getListOfTasks')->willReturn([new Task('CIL', 'buyer', 'Support', [], [new Timing('2026-01-12 09:00:00', '2026-01-12 10:00:00')])]);
        $filesystem = $this->createMock(FilesystemInterface::class);
        $filesystem->method('numberOfFilesInExportDirectory')->willReturn(0);
        $filesystem->expects(self::once())->method('dumpExport');
        $merger = $this->createMock(PdfAndXmlMerger::class);
        $merger->expects(self::once())->method('merge')->with('pdf', 'xml')->willReturn('merged');
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturn('Document');
        $dispatcher = new EventDispatcher();
        $application = new Application(new SystemClock(), $renderer, $xmlRenderer, $processor, $dispatcher, $translator, $filesystem, [$organization], $sender, $merger);
        $dispatcher->addSubscriber($application);
        $application->generateTimesheet(new GenerateTimesheet(
            timesheetId: '2ce08e7e-67b4-4a81-b819-ea7a2590346d',
            project: 'CIL',
            approvedBy: ['name' => 'Example Approver', 'company' => 'Example Buyer'],
            providedBy: ['name' => $sender['name'], 'street' => $sender['street'], 'location' => $sender['location']],
            performancePeriod: 'LAST_MONTH',
            startDate: '2026-02-01',
            templateId: $override,
        ));

        self::assertCount(2, $components);
        self::assertInstanceOf(TimesheetReport::class, $components[0]);
        self::assertSame('pdf/timesheet/report.html.twig', $components[0]->template());
        self::assertInstanceOf(Invoice::class, $components[1]);
        self::assertSame($expected === 'marc' ? 'pdf/marc/invoice.html.twig' : 'pdf/timesheet/invoice.html.twig', $components[1]->template());
    }

    /**
     * @return iterable<string, array{?string, ?string, string}>
     */
    public static function templates(): iterable
    {
        yield 'classic fallback' => [null, null, 'classic'];
        yield 'configured marc' => [null, 'marc', 'marc'];
        yield 'configured classic' => [null, 'classic', 'classic'];
        yield 'explicit marc' => ['marc', null, 'marc'];
        yield 'marc overrides classic' => ['marc', 'classic', 'marc'];
        yield 'classic overrides marc' => ['classic', 'marc', 'classic'];
    }
}
