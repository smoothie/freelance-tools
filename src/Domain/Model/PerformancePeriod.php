<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Domain\Model;

class PerformancePeriod
{
    public const string PERIOD_MONTH_LAST = 'LAST_MONTH';
    public const string PERIOD_MONTH_CURRENT = 'CURRENT_MONTH';
    public const string PERIOD_LAST_TWO_MONTHS = 'LAST_TWO_MONTHS';

    public const string FIRST_DAY_OF_THE_MONTH = 'FIRST_DAY_OF_THE_MONTH';
    public const string FIRST_DAY_OF_LAST_MONTH = 'FIRST_DAY_OF_LAST_MONTH';

    public const string LAST_DAY_OF_THE_MONTH = 'LAST_DAY_OF_THE_MONTH';
    public const string LAST_DAY_OF_LAST_MONTH = 'LAST_DAY_OF_LAST_MONTH';

    public const string FIRST_DAY_OF_LAST_BEFORE_MONTH = 'FIRST_DAY_OF_LAST_BEFORE_MONTH';

    public function __construct(
        private string $performancePeriodId,
        private string $performancePeriodStartsOn,
        private string $performancePeriodEndsOn,
    ) {
    }

    public static function fromString(string $performancePeriodId): self
    {
        $performancePeriodStartsOn = match (mb_strtoupper($performancePeriodId)) {
            self::PERIOD_MONTH_CURRENT => self::FIRST_DAY_OF_THE_MONTH,
            self::PERIOD_MONTH_LAST => self::FIRST_DAY_OF_LAST_MONTH,
            self::PERIOD_LAST_TWO_MONTHS => self::FIRST_DAY_OF_LAST_BEFORE_MONTH,
            default => throw new \LogicException(
                \sprintf('Unsupported performance period ID: "%s"', $performancePeriodId),
            ),
        };

        $performancePeriodEndsOn = match (mb_strtoupper($performancePeriodId)) {
            self::PERIOD_MONTH_CURRENT => self::LAST_DAY_OF_THE_MONTH,
            self::PERIOD_MONTH_LAST, self::PERIOD_LAST_TWO_MONTHS => self::LAST_DAY_OF_LAST_MONTH,
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
        return match (mb_strtoupper($this->performancePeriodId)) {
            self::PERIOD_LAST_TWO_MONTHS,
            self::PERIOD_MONTH_CURRENT,
            self::PERIOD_MONTH_LAST => FilterCriteria::fromString(
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
