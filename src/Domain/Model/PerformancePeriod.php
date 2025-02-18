<?php

declare(strict_types=1);

namespace App\Domain\Model;

class PerformancePeriod
{
    public function __construct(
        private string $performancePeriodId,
        private string $performancePeriodStartsOn,
        private string $performancePeriodEndsOn,
    ) {
    }

    public static function fromString(string $performancePeriodId): self
    {
        $performancePeriodStartsOn = match ($performancePeriodId) {
            'CURRENT_MONTH' => 'FIRST_DAY_OF_THE_MONTH',
            'LAST_MONTH' => 'FIRST_DAY_OF_LAST_MONTH',
            default => throw new \LogicException(
                \sprintf('Unsupported performance period ID: "%s"', $performancePeriodId),
            ),
        };

        $performancePeriodEndsOn = match ($performancePeriodId) {
            'CURRENT_MONTH' => 'LAST_DAY_OF_THE_MONTH',
            'LAST_MONTH' => 'LAST_DAY_OF_LAST_MONTH',
            default => throw new \LogicException(
                \sprintf('Unsupported performance period ID: "%s"', $performancePeriodId),
            ),
        };

        return new self(
            performancePeriodId: $performancePeriodId,
            performancePeriodStartsOn: $performancePeriodStartsOn,
            performancePeriodEndsOn: $performancePeriodEndsOn,
        );
    }

    public function performancePeriodId(): string
    {
        return $this->performancePeriodId;
    }

    public function performancePeriodStartsOn(): string
    {
        return $this->performancePeriodStartsOn;
    }

    public function performancePeriodEndsOn(): string
    {
        return $this->performancePeriodEndsOn;
    }

    public function filterCriteria(): FilterCriteria
    {
        return match ($this->performancePeriodId) {
            'CURRENT_MONTH',
            'LAST_MONTH' => FilterCriteria::fromString(
                \sprintf(
                    '(startDate >= :%s) AND (endDate <= :%s)',
                    $this->performancePeriodStartsOn,
                    $this->performancePeriodEndsOn,
                ),
            ),
            default => FilterCriteria::fromString(''),
        };
    }
}
