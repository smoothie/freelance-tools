<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Unit\Application;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use Smoothie\FreelanceTools\Application\Application;
use Smoothie\FreelanceTools\Application\Clock;
use Smoothie\FreelanceTools\Application\FilesystemInterface;
use Smoothie\FreelanceTools\Application\PdfAndXmlMerger;
use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;
use Smoothie\FreelanceTools\Domain\Service\ComponentRenderer;
use Smoothie\FreelanceTools\Domain\Service\WorkTimeProcessor;
use Smoothie\FreelanceTools\Tests\BasicTestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Small]
#[Group('application')]
#[CoversClass(Application::class)]
#[UsesClass(DateTime::class)]
class ApplicationTest extends BasicTestCase
{
    #[DataProvider('provideInvoiceNumbers')]
    public function testNextInvoiceNumberUsesTheBillingMonth(string $billingDate, int $existingFiles, string $prefix, string $expected): void
    {
        $filesystem = $this->createMock(FilesystemInterface::class);
        $filesystem->expects(self::once())
            ->method('numberOfFilesInExportDirectory')
            ->with('*'.$prefix.'-*.pdf')
            ->willReturn($existingFiles);
        $application = $this->createApplication($this->createStub(Clock::class), $filesystem);

        self::assertSame($expected, $application->getNextInvoiceNumber(DateTime::fromDateString($billingDate)));
    }

    #[DataProvider('provideDateFilters')]
    public function testDateFiltersDelegateToTheClock(string $filter, string $method): void
    {
        $date = DateTime::fromDateString('2024-02-29');
        $clock = $this->createMock(Clock::class);
        $clock->expects(self::once())->method($method)->willReturn($date);
        $application = $this->createApplication($clock, $this->createStub(FilesystemInterface::class));

        self::assertSame($date, $application->applyDateFilter($filter));
    }

    #[DataProvider('provideUnknownDateFilters')]
    public function testUnknownDateFiltersReturnNull(string $filter): void
    {
        $clock = $this->createMock(Clock::class);
        foreach (self::provideDateFilters() as [$knownFilter, $method]) {
            $clock->expects(self::never())->method($method);
        }
        $application = $this->createApplication($clock, $this->createStub(FilesystemInterface::class));

        self::assertNull($application->applyDateFilter($filter));
    }

    public static function provideInvoiceNumbers(): \Generator
    {
        yield 'first invoice in january' => ['2025-01-01', 0, '202501', '202501-01'];
        yield 'last day of december stays in december' => ['2024-12-31', 9, '202412', '202412-10'];
        yield 'leap day stays in february' => ['2024-02-29', 1, '202402', '202402-02'];
        yield 'month end stays in march' => ['2025-03-31', 99, '202503', '202503-100'];
    }

    public static function provideDateFilters(): \Generator
    {
        yield 'first day of last month' => ['FIRST_DAY_OF_LAST_MONTH', 'firstDayOfLastMonth'];
        yield 'last day of last month' => ['LAST_DAY_OF_LAST_MONTH', 'lastDayOfLastMonth'];
        yield 'first day of current month' => ['FIRST_DAY_OF_THE_MONTH', 'firstDayOfTheMonth'];
        yield 'last day of current month' => ['LAST_DAY_OF_THE_MONTH', 'lastDayOfTheMonth'];
        yield 'first day two months ago' => ['FIRST_DAY_OF_LAST_BEFORE_MONTH', 'firstDayOfLastBeforeMonth'];
        yield 'case insensitive' => ['first_day_of_last_before_month', 'firstDayOfLastBeforeMonth'];
    }

    public static function provideUnknownDateFilters(): \Generator
    {
        yield 'empty' => [''];
        yield 'unknown' => ['UNKNOWN_DATE_FILTER'];
    }

    private function createApplication(Clock $clock, FilesystemInterface $filesystem): Application
    {
        return new Application(
            clock: $clock,
            componentRenderer: $this->createStub(ComponentRenderer::class),
            invoiceComponentRenderer: $this->createStub(ComponentRenderer::class),
            workTimeProcessor: $this->createStub(WorkTimeProcessor::class),
            eventDispatcher: $this->createStub(EventDispatcherInterface::class),
            translator: $this->createStub(TranslatorInterface::class),
            filesystem: $filesystem,
            organizations: [],
            providedBy: [],
            pdfAndXmlMerger: $this->createStub(PdfAndXmlMerger::class),
        );
    }
}
