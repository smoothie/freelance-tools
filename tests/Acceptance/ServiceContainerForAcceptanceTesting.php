<?php

declare(strict_types=1);

namespace App\Tests\Acceptance;

use App\Application\Application;
use App\Application\ApplicationInterface;
use App\Application\Clock;
use App\Application\GeneratedATimesheetReport;
use App\Infrastructure\SystemClock;
use App\Infrastructure\Twig\TwigComponentRenderer;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Webmozart\Assert\Assert;

class ServiceContainerForAcceptanceTesting
{
    private ?InMemoryWorkTimeProcessor $workTimeProcessor = null;
    private ?InMemoryComponentRenderer $componentRenderer = null;

    public function __construct(
        private TwigComponentRenderer $twigComponentRenderer,
        private SystemClock $clock,
        private EventDispatcherInterface $eventDispatcher,
    ) {
        $eventDispatcher->addListener(
            ApplicationInterface::EVENT_GENERATED_TIMESHEET,
            static function (GeneratedATimesheetReport $event) {
                printf('we dispatched and received the event "%s"', get_debug_type($event));
            },
        );
    }

    public function application(): ApplicationInterface
    {
        return new Application(
            clock: $this->clock(),
            componentRenderer: $this->componentRenderer(),
            workTimeProcessor: $this->workTimeProcessor(),
            eventDispatcher: $this->eventDispatcher(),
        );
    }

    public function eventDispatcher(): TraceableEventDispatcher
    {
        Assert::isInstanceOf($this->eventDispatcher, TraceableEventDispatcher::class);
        \assert($this->eventDispatcher instanceof TraceableEventDispatcher);

        return $this->eventDispatcher;
    }

    public function componentRenderer(): InMemoryComponentRenderer
    {
        if ($this->componentRenderer === null) {
            $this->componentRenderer = new InMemoryComponentRenderer($this->twigComponentRenderer);
        }

        return $this->componentRenderer;
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

    public function setCurrentTime(string $dateTime): void
    {
        $clock = $this->clock();

        $clock->setCurrentDate($dateTime);
    }
}
