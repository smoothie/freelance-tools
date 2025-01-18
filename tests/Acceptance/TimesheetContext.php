<?php

declare(strict_types=1);

namespace App\Tests\Acceptance;

use Behat\Gherkin\Node\TableNode;
use LeanpubBookClub\Domain\Model\Purchase\PurchaseWasImported;
use PHPUnit\Framework\Assert;

final class TimesheetContext extends FeatureContext
{
    /**
     * @When someone generated a timesheet but no tasks exist
     */
    public function theGenerationStarts(): void
    {
        $this->clearEvents();

        $this->application()->importAllPurchases();
    }

    /**
     * @Then the report should not be generated
     */
    public function noTimesheetShouldBeGenerated(TableNode $table): void
    {
        Assert::assertEquals([], $this->purchaseImportedEvents());
    }

    /**
     * @Then no purchases should have been imported
     */
    public function noPurchasesShouldHaveBeenImported(): void
    {
        Assert::assertEquals([], $this->purchaseImportedEvents());
    }

    /**
     * @return array<PurchaseWasImported>
     */
    private function purchaseImportedEvents(): array
    {
        return array_filter(
            $this->dispatchedEvents(),
            static function (object $event): bool {
                return $event instanceof PurchaseWasImported;
            },
        );
    }
}
