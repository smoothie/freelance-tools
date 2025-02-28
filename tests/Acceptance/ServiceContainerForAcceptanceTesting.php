<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Acceptance;

use Smoothie\FreelanceTools\Application\Application;
use Smoothie\FreelanceTools\Application\ApplicationInterface;
use Smoothie\FreelanceTools\Application\Clock;
use Smoothie\FreelanceTools\Application\GeneratedAnInvoice;
use Smoothie\FreelanceTools\Application\GeneratedATimesheetReport;
use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;
use Smoothie\FreelanceTools\Infrastructure\DomPdf\DomPdfBuilder;
use Smoothie\FreelanceTools\Infrastructure\DomPdf\DomPdfComponentRenderer;
use Smoothie\FreelanceTools\Infrastructure\horstoeko\EInvoiceBuilder;
use Smoothie\FreelanceTools\Infrastructure\horstoeko\ZugferdComponentRenderer;
use Smoothie\FreelanceTools\Infrastructure\horstoeko\ZugferdDocumentMerger;
use Smoothie\FreelanceTools\Infrastructure\SystemClock;
use Smoothie\FreelanceTools\Infrastructure\Twig\TwigComponentRenderer;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

class ServiceContainerForAcceptanceTesting
{
    private ?InMemoryWorkTimeProcessor $workTimeProcessor = null;
    private ?InMemoryComponentRenderer $componentRenderer = null;
    private ?InMemoryFilesystem $filesystem = null;

    private ?GeneratedATimesheetReport $lastGeneratedATimesheetReport = null;

    private ?ApplicationInterface $application = null;

    public function __construct(
        private TwigComponentRenderer $twigComponentRenderer,
        private DomPdfBuilder $domPdfBuilder,
        private SystemClock $clock,
        private EventDispatcherInterface $eventDispatcher,
        private TranslatorInterface $translator,
        #[Autowire(param: 'tools.default_providedBy')]
        private array $providedBy,
    ) {
        $eventDispatcher->addListener(
            ApplicationInterface::EVENT_GENERATED_TIMESHEET,
            function (GeneratedATimesheetReport $event) {
                $this->lastGeneratedATimesheetReport = $event;
                printf('we dispatched and received the event "%s"', get_debug_type($event));
            },
        );

        $eventDispatcher->addListener(
            ApplicationInterface::EVENT_GENERATED_INVOICE,
            static function (GeneratedAnInvoice $event) {
                printf('we dispatched and received the event "%s"', get_debug_type($event));
            },
        );
    }

    public function application(): ApplicationInterface
    {
        if ($this->application === null) {
            $this->application = new Application(
                clock: $this->clock(),
                componentRenderer: $this->componentRenderer(),
                invoiceComponentRenderer: $this->invoiceComponentRenderer(),
                workTimeProcessor: $this->workTimeProcessor(),
                eventDispatcher: $this->eventDispatcher(),
                translator: $this->translator,
                filesystem: $this->filesystem(),
                organizations: $this->organizations(),
                providedBy: $this->providedBy(),
                pdfAndXmlMerger: $this->pdfAndXmlMerger(),
            );
        }

        return $this->application;
    }

    public function providedBy(): array
    {
        return $this->providedBy;
    }

    public function organizations(): array
    {
        return [
            [
                'project' => 'cheesecake-agile',
                'name' => 'Cheese Squad',
                'street' => 'A Street',
                'location' => '66113 Saarbrücken',
                'country' => 'DE',
                'vatId' => 'DE000000000',
                'description' => 'Some project specific description',
                'pricePerHour' => 133.7,
                'taxRate' => 19.0,
                'termOfPaymentInDays' => 30.0,
            ],
        ];
    }

    public function eventDispatcher(): TraceableEventDispatcher
    {
        Assert::isInstanceOf($this->eventDispatcher, TraceableEventDispatcher::class);
        \assert($this->eventDispatcher instanceof TraceableEventDispatcher);

        return $this->eventDispatcher;
    }

    public function componentRenderer(): DomPdfComponentRenderer
    {
        return new DomPdfComponentRenderer($this->twigComponentRenderer, $this->domPdfBuilder, $this->filesystem());
    }

    public function invoiceComponentRenderer(): ZugferdComponentRenderer
    {
        return new ZugferdComponentRenderer(new EInvoiceBuilder(), $this->filesystem());
    }

    public function pdfAndXmlMerger(): ZugferdDocumentMerger
    {
        return new ZugferdDocumentMerger();
    }

    public function filesystem(): InMemoryFilesystem
    {
        if ($this->filesystem === null) {
            $this->filesystem = new InMemoryFilesystem();
        }

        return $this->filesystem;
    }

    public function translator(): TranslatorInterface
    {
        return $this->translator;
    }

    public function workTimeProcessor(): InMemoryWorkTimeProcessor
    {
        if ($this->workTimeProcessor === null) {
            $this->workTimeProcessor = new InMemoryWorkTimeProcessor();
        }

        return $this->workTimeProcessor;
    }

    public function clock(): Clock
    {
        return $this->clock;
    }

    public function setCurrentTime(DateTime $dateTime): void
    {
        $clock = $this->clock();

        $clock->setCurrentDate($dateTime);
    }

    public function lastGeneratedATimesheetReport(): ?GeneratedATimesheetReport
    {
        return $this->lastGeneratedATimesheetReport;
    }
}
