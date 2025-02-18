<?php

declare(strict_types=1);

namespace App\Tests\Acceptance;

use App\Application\ApplicationInterface;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

abstract class FeatureContext implements Context
{
    public function __construct(
        private ServiceContainerForAcceptanceTesting $serviceContainer,
        #[Autowire(param: 'tools.default_providedBy')]
        private array $providedBy,
    ) {
        $this->serviceContainer->setCurrentTime('2025-02-17 14:09:00');
    }

    protected function application(): ApplicationInterface
    {
        return $this->serviceContainer->application();
    }

    protected function serviceContainer(): ServiceContainerForAcceptanceTesting
    {
        return $this->serviceContainer;
    }

    public function defaultProvidedBy(): array
    {
        return $this->providedBy;
    }

    public function eventHasBeenDispatched(string $eventName): void
    {
        foreach ($this->serviceContainer()->eventDispatcher()->getCalledListeners() as $calledListener) {
            if ($calledListener['event'] === $eventName) {
                return;
            }
        }

        Assert::assertTrue(false, \sprintf('The event "%s" has been not dispatched', $eventName));
    }
}
