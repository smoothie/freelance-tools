<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Unit\Infrastructure\Twig;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;
use Smoothie\FreelanceTools\Infrastructure\Twig\TwigExtension;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Small]
#[CoversClass(TwigExtension::class)]
#[UsesClass(DateTime::class)]
class TwigExtensionTest extends TestCase
{
    public function testThatPerformancePeriodsUseCalendarYears(): void
    {
        $extension = new TwigExtension($this->createStub(TranslatorInterface::class));
        $date = DateTime::fromDateString('2019-12-30');

        self::assertSame([], $extension->toPerformancePeriod([]));
        self::assertSame(['Dezember 2019'], $extension->toPerformancePeriod([$date]));
        self::assertSame(
            ['Dezember 2019', 'Januar 2020'],
            $extension->toPerformancePeriod([$date, DateTime::fromDateString('2020-01-01')]),
        );
    }
}
