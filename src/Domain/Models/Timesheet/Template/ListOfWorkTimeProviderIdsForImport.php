<?php

declare(strict_types=1);

namespace App\Domain\Models\Timesheet\Template;

use App\Domain\Models\WorkTime\WorkTimeProviderId;
use Webmozart\Assert\Assert;

class ListOfWorkTimeProviderIdsForImport
{
    /**
     * @var array<WorkTimeProviderId>
     */
    private array $workTimeProviderIds;

    private function __construct()
    {
    }

    /**
     * @param array<WorkTimeProviderId> $workTimeProviderIds
     */
    public static function fromArray(array $workTimeProviderIds = []): self
    {
        Assert::allIsInstanceOf($workTimeProviderIds, WorkTimeProviderId::class);

        $list = new self();
        $list->workTimeProviderIds = $workTimeProviderIds;

        return $list;
    }

    public function workTimeProviderIds(): array
    {
        return $this->workTimeProviderIds;
    }
}
