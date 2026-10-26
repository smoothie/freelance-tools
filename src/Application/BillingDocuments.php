<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Application;

use Smoothie\FreelanceTools\Domain\Model\BilledBy;
use Smoothie\FreelanceTools\Domain\Model\BilledTo;
use Smoothie\FreelanceTools\Domain\Model\Common\Amount;
use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;
use Smoothie\FreelanceTools\Domain\Model\ContactInformation;
use Smoothie\FreelanceTools\Domain\Model\DueDate;
use Smoothie\FreelanceTools\Domain\Model\FilterCriteria;
use Smoothie\FreelanceTools\Domain\Model\Invoice;
use Smoothie\FreelanceTools\Domain\Model\InvoiceId;
use Smoothie\FreelanceTools\Domain\Model\InvoiceItem;
use Smoothie\FreelanceTools\Domain\Model\PaymentInformation;
use Smoothie\FreelanceTools\Domain\Model\PerformancePeriod;
use Smoothie\FreelanceTools\Domain\Model\ProjectId;
use Smoothie\FreelanceTools\Domain\Service\WorkTimeProcessor;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Webmozart\Assert\Assert;

class BillingDocuments
{
    /**
     * @param list<array{project: string, customerId?: string|null, templateId?: string, name: string, street: string, location: string, country: string, vatId: string, description: string, taxRate: float, pricePerHour: float, termOfPaymentInDays: int, orderNumber?: string|null}> $organizations
     * @param array{name: string, street: string, location: string, vatId: string, country: string, phone: string, mail: string, web: string, bank: string, iban: string, bic: string} $providedBy
     */
    public function __construct(
        private ApplicationInterface $application,
        private Clock $clock,
        private WorkTimeProcessor $workTimeProcessor,
        #[Autowire(param: 'tools.organizations')]
        private array $organizations,
        #[Autowire(param: 'tools.default_providedBy')]
        private array $providedBy,
    ) {
    }

    public function generate(CreateBillingDocument $command): Invoice
    {
        $invoice = $this->create($command);
        if ($invoice->documentType() === 'quote') {
            $this->application->renderComponent($invoice);
        } else {
            $this->application->buildAnEInvoice($invoice);
        }

        return $invoice;
    }

    public function create(CreateBillingDocument $command): Invoice
    {
        $organizations = array_values(array_filter(
            $this->organizations,
            static fn (array $organization): bool => ($organization['customerId'] ?? $organization['project']) === $command->customerId,
        ));
        Assert::notEmpty($organizations, 'Unknown customer ID.');
        $organization = $organizations[0];
        $identityKeys = ['name', 'street', 'location', 'country', 'vatId', 'taxRate', 'termOfPaymentInDays', 'orderNumber', 'templateId'];
        foreach ($organizations as $candidate) {
            foreach ($identityKeys as $key) {
                Assert::same($candidate[$key] ?? null, $organization[$key] ?? null, 'Customer billing settings differ across projects: '.$key);
            }
        }
        $items = $command->items;
        $deliveredAt = $command->deliveredAt;
        if ($command->projects !== []) {
            [$projectItems, $periodDates] = $this->projectItems($command, $organizations);
            $items = [...$items, ...$projectItems];
            Assert::true($deliveredAt === [] || $deliveredAt === $periodDates, 'Service dates must match the tracked performance period.');
            $deliveredAt = $periodDates;
        }
        Assert::notEmpty($items, 'At least one billable position is required.');
        $billingDate = DateTime::fromDateString($command->billingDate);
        $days = $command->termOfPaymentInDays ?? $organization['termOfPaymentInDays'];
        Assert::greaterThanEq($days, 0);
        $template = $command->templateId ?? ($organization['templateId'] ?? 'classic');
        $title = match ($command->documentType) {
            'invoice' => 'Rechnung Nr. ',
            'advance' => 'Anzahlungsrechnung Nr. ',
            'final' => 'Schlussrechnung Nr. ',
            'quote' => 'Angebot Nr. ',
            default => throw new \InvalidArgumentException('Unsupported document type.'),
        }.$command->number;
        $seller = $this->providedBy;
        $invoice = new Invoice(
            title: $title,
            projectId: ProjectId::fromString($command->customerId),
            invoiceId: InvoiceId::fromString($command->number),
            description: $organization['description'],
            taxRate: Amount::fromString((string) $organization['taxRate']),
            billingDate: $billingDate,
            dueDate: new DueDate(DateTime::fromDateTime($billingDate->asPhpDateTime()->modify('+'.$days.' days')), $days),
            billedBy: new BilledBy(
                name: $seller['name'],
                street: $seller['street'],
                location: $seller['location'],
                vatId: $seller['vatId'],
                country: $seller['country'],
                contactInformation: new ContactInformation($seller['phone'], $seller['mail'], $seller['web']),
                paymentInformation: new PaymentInformation($seller['bank'], $seller['iban'], $seller['bic']),
            ),
            billedTo: new BilledTo($organization['name'], $organization['street'], $organization['location'], $organization['vatId'], $organization['country']),
            deliveredAt: array_map(static fn (string $date): DateTime => DateTime::fromDateString($date), $deliveredAt),
            orderNumber: (string) ($organization['orderNumber'] ?? ''),
            templateId: $template,
            documentType: $command->documentType,
            reference: $command->reference,
            documentNotes: $command->documentNotes,
            advancePayments: $command->advancePayments,
        );
        foreach ($items as $index => $item) {
            $invoice->addInvoiceItem($item->withPosition($index + 1));
        }
        $invoice->payableAmount();

        return $invoice;
    }

    /**
     * @param list<array{project: string, customerId?: string|null, templateId?: string, name: string, street: string, location: string, country: string, vatId: string, description: string, taxRate: float, pricePerHour: float, termOfPaymentInDays: int, orderNumber?: string|null}> $organizations
     *
     * @return array{list<InvoiceItem>, list<string>}
     */
    private function projectItems(CreateBillingDocument $command, array $organizations): array
    {
        $period = PerformancePeriod::fromString($command->performancePeriod);
        $clock = clone $this->clock;
        $clock->setCurrentDate(DateTime::fromDateString($command->startDate ?? $command->billingDate));
        $start = match ($period->performancePeriodStartsOn()) {
            PerformancePeriod::FIRST_DAY_OF_THE_MONTH => $clock->firstDayOfTheMonth(),
            PerformancePeriod::FIRST_DAY_OF_LAST_MONTH => $clock->firstDayOfLastMonth(),
            PerformancePeriod::FIRST_DAY_OF_LAST_BEFORE_MONTH => $clock->firstDayOfLastBeforeMonth(),
            default => throw new \LogicException('Unsupported performance period.'),
        };
        $end = $period->performancePeriodEndsOn() === PerformancePeriod::LAST_DAY_OF_THE_MONTH
            ? $clock->lastDayOfTheMonth()
            : $clock->lastDayOfLastMonth();
        $items = [];
        foreach ($command->projects as $project) {
            $matches = array_values(array_filter($organizations, static fn (array $organization): bool => $organization['project'] === $project));
            Assert::count($matches, 1, 'Project must belong to the selected customer exactly once: '.$project);
            $organization = $matches[0];
            $filter = FilterCriteria::fromString(
                \sprintf('(startDate >= \'%s\') AND (endDate <= \'%s\')', $start->asDateString(), $end->asDateString()),
            );
            $filter->withExactProject(ProjectId::fromString($project));
            $lists = $this->application->getListOfTasks($filter, $this->workTimeProcessor);
            $seconds = 0;
            foreach ($lists->lists() as $list) {
                Assert::same($project, $list->projectId()->asString(), 'Unexpected project returned by time provider.');
                $seconds += $list->totalDuration()->asInt();
            }
            Assert::greaterThan($seconds, 0, 'No tracked time for project: '.$project);
            $items[] = InvoiceItem::fromTrackedSeconds(\count($command->items) + \count($items) + 1, $seconds, $project.' — '.$organization['description'], Amount::fromString((string) $organization['pricePerHour']));
        }

        return [$items, [$start->asDateString(), $end->asDateString()]];
    }
}
