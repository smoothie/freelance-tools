<?php

declare(strict_types=1);

namespace App\Tests\Acceptance;

use App\Application\ApplicationInterface;
use Behat\Behat\Context\Context;

abstract class FeatureContext implements Context
{
    private ServiceContainerForAcceptanceTesting $serviceContainer;

    public function __construct()
    {
        $this->serviceContainer = new ServiceContainerForAcceptanceTesting();
    }

    /**
     * @return array<object>
     */
    protected function dispatchedEvents(): array
    {
        return $this->serviceContainer->eventDispatcherSpy()->dispatchedEvents();
    }

    protected function clearEvents(): void
    {
        $this->serviceContainer->eventDispatcherSpy()->clearEvents();
    }

    protected function application(): ApplicationInterface
    {
        return $this->serviceContainer->application();
    }

    protected function serviceContainer(): ServiceContainerForAcceptanceTesting
    {
        return $this->serviceContainer;
    }
}
