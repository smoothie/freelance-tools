<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Application;

use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;
use Smoothie\FreelanceTools\Domain\Model\Component;
use Smoothie\FreelanceTools\Domain\Model\FilterCriteria;
use Smoothie\FreelanceTools\Domain\Model\Group\ListOfTasksInProjects;
use Smoothie\FreelanceTools\Domain\Model\Invoice;
use Smoothie\FreelanceTools\Domain\Model\InvoiceId;
use Smoothie\FreelanceTools\Domain\Model\ProjectId;
use Smoothie\FreelanceTools\Domain\Model\TimesheetReport;
use Smoothie\FreelanceTools\Domain\Service\ComponentRenderer;
use Smoothie\FreelanceTools\Domain\Service\WorkTimeProcessor;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

class Application implements ApplicationInterface, EventSubscriberInterface
{
    /**
     * @param list<array{ project: string, name: string, street: string, location: string, country: string, vatId: string, description: string, taxRate: float, pricePerHour: float, termOfPaymentInDays: int}> $organizations
     * @param array{name: string, street: string, location: string, vatId: string, country: string, phone: string, mail: string, web: string, bank: string, iban: string, bic: string} $providedBy
     */
    public function __construct(
        private Clock $clock,
        private ComponentRenderer $componentRenderer,
        private ComponentRenderer $invoiceComponentRenderer,
        private WorkTimeProcessor $workTimeProcessor,
        private EventDispatcherInterface $eventDispatcher,
        private TranslatorInterface $translator,
        private FilesystemInterface $filesystem,
        #[Autowire(param: 'tools.organizations')]
        private array $organizations,
        #[Autowire(param: 'tools.default_providedBy')]
        private array $providedBy,
        private PdfAndXmlMerger $pdfAndXmlMerger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            self::EVENT_GENERATED_TIMESHEET => 'whenATimesheetReportWasGenerated',
        ];
    }

    public function generateTimesheet(GenerateTimesheet $command): void
    {
        $performancePeriod = $command->performancePeriod();
        $this->clock->setCurrentDate($command->startDate());
        $startDate = $this->applyDateFilter($performancePeriod->performancePeriodStartsOn());
        $endDate = $this->applyDateFilter($performancePeriod->performancePeriodEndsOn());

        $filter = $command->performancePeriod()->filterCriteria();
        $filter->withExactProject($command->project());

        $listOfTasksInProjects = $this->getListOfTasks($filter, $this->workTimeProcessor);

        Assert::count($listOfTasksInProjects->lists(), 1);
        $listOfTasks = current($listOfTasksInProjects->lists());

        $title = $this->replaceTimesheetTitle($startDate);

        $report = new TimesheetReport(
            timesheetReportId: $command->timesheetId(),
            projectId: $command->project(),
            title: $title,
            approvedBy: $command->approvedBy(),
            approvedAt: $command->approvedAt(),
            billedTo: $command->billedTo(),
            billedBy: $command->billedBy(),
            providedBy: $command->providedBy(),
            startDate: $startDate,
            endDate: $endDate,
            totalDuration: $listOfTasks->totalDuration(),
            listOfTasks: $listOfTasks,
        );

        $rendered = $this->renderComponent($report);

        $this->eventDispatcher->dispatch(
            new GeneratedATimesheetReport($report, $command->exportFormat(), $rendered),
            self::EVENT_GENERATED_TIMESHEET,
        );
    }

    public function generateInvoice(GenerateInvoice $command): void
    {
        $title = $this->replaceInvoiceTitle($command->invoiceId());

        $invoice = new Invoice(
            title: $title,
            projectId: $command->project(),
            invoiceId: $command->invoiceId(),
            description: $command->description(),
            taxRate: $command->taxRate(),
            billingDate: $command->billingDate(),
            dueDate: $command->dueDate(),
            billedBy: $command->billedBy(),
            billedTo: $command->billedTo(),
            deliveredAt: $command->deliveredAt(),
        );

        foreach ($command->invoiceItems() as $item) {
            $invoice->addInvoiceItem($item);
        }

        $pdf = $this->buildAnEInvoice($invoice);

        $this->eventDispatcher->dispatch(
            new GeneratedAnInvoice($invoice, $command->exportFormat(), $pdf),
            self::EVENT_GENERATED_INVOICE,
        );
    }

    public function buildAnEInvoice(Invoice $invoice): string
    {
        $pdf = $this->renderComponent($invoice);
        $xml = $this->invoiceComponentRenderer->render($invoice);
        $new = $this->pdfAndXmlMerger->merge($pdf, $xml);

        $this->filesystem->dumpExport($invoice->fileName('pdf'), $new);

        return $new;
    }

    public function whenATimesheetReportWasGenerated(GeneratedATimesheetReport $event): void
    {
        $report = $event->timesheetReport();
        $organization = $this->identifyOrganization($report->projectId());
        $billingDate = DateTime::fromDateTime($this->clock->currentTime());
        $invoiceId = $this->getNextInvoiceNumber($billingDate);

        $command = new GenerateInvoice(
            invoiceId: $invoiceId,
            project: $report->projectId()->asString(),
            description: $organization['description'],
            durationInSeconds: $report->totalDuration()->asInt(),
            deliveredAt: $report->endDate()->asDateString(),
            taxRate: (float) $organization['taxRate'],
            billingDate: $billingDate->asDateString(),
            pricePerHour: (float) $organization['pricePerHour'],
            termOfPaymentInDays: (int) $organization['termOfPaymentInDays'],
            billedBy: $this->providedBy,
            billedTo: $organization,
        );

        $this->generateInvoice($command);
    }

    public function getNextInvoiceNumber(DateTime $billingDate): string
    {
        $prefix = $billingDate->asPhpDateTime()->format('Ym');
        $pattern = \sprintf('*%s-*.pdf', $prefix);
        $lastIncrementor = $this->filesystem->numberOfFilesInExportDirectory($pattern);
        $nextIncrementor = $lastIncrementor + 1;

        return \sprintf('%s-%s', $prefix, str_pad((string) $nextIncrementor, 2, '0', \STR_PAD_LEFT));
    }

    public function applyDateFilter(string $filter): ?DateTime
    {
        return match (mb_strtoupper($filter)) {
            'FIRST_DAY_OF_LAST_MONTH' => $this->clock->firstDayOfLastMonth(),
            'LAST_DAY_OF_LAST_MONTH' => $this->clock->lastDayOfLastMonth(),
            'FIRST_DAY_OF_THE_MONTH' => $this->clock->firstDayOfTheMonth(),
            'LAST_DAY_OF_THE_MONTH' => $this->clock->lastDayOfTheMonth(),
            default => null,
        };
    }

    public function getListOfTasks(FilterCriteria $filter, WorkTimeProcessor $workTimeProcessor): ListOfTasksInProjects
    {
        return ListOfTasksInProjects::fromTasks($workTimeProcessor->getListOfTasks($filter));
    }

    public function renderComponent(Component $component): string
    {
        return $this->componentRenderer->render($component);
    }

    /**
     * @return array{ project: string, name: string, street: string, location: string, country: string, vatId: string, description: string, taxRate: float, pricePerHour: float, termOfPaymentInDays: int}
     */
    private function identifyOrganization(ProjectId $projectId): array
    {
        foreach ($this->organizations as $organization) {
            if ($projectId->asString() === $organization['project']) {
                return $organization;
            }
        }

        throw new \RuntimeException(\sprintf('No organization found for project "%s"', $projectId->asString()));
    }

    private function replaceInvoiceTitle(InvoiceId $invoiceId): string
    {
        return $this->translator->trans(
            'invoice.title',
            [
                'INVOICE_NUMBER' => $invoiceId->asString(),
            ],
            domain: 'tools',
        );
    }

    private function replaceTimesheetTitle(DateTime $dateTime): string
    {
        return $this->translator->trans(
            'timesheet.title',
            ['MM.YYYY' => $dateTime->asPhpDateTime()->format('m.Y')],
            domain: 'tools',
        );
    }
}
