<?php

declare(strict_types=1);

namespace Smoothie\FreelanceTools\Tests\Acceptance;

use Behat\Behat\Context\Context;
use Smoothie\FreelanceTools\Application\ApplicationInterface;
use Smoothie\FreelanceTools\Domain\Model\Common\DateTime;
use Webmozart\Assert\Assert;

abstract class FeatureContext implements Context
{
    public const string DATE_TIME_INSTANTIATED_AT = '2025-02-17 14:09:00';

    public function __construct(
        private ServiceContainerForAcceptanceTesting $serviceContainer,
    ) {
        $this->serviceContainer->setCurrentTime(DateTime::fromString(self::DATE_TIME_INSTANTIATED_AT));
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
        return $this->serviceContainer->providedBy();
    }

    public function anOrganization(): array
    {
        return [
            'project' => 'cheesecake-agile',
            'name' => 'Cheese Squad',
            'street' => 'A Street',
            'location' => '66113 Saarbrücken',
            'country' => 'DE',
            'vatId' => 'DE000000000',
            'description' => 'Some project specific description',
        ];
    }

    public function eventHasBeenDispatched(string $eventName): void
    {
        foreach ($this->serviceContainer()->eventDispatcher()->getCalledListeners() as $calledListener) {
            if ($calledListener['event'] === $eventName) {
                return;
            }
        }

        Assert::true(false, \sprintf('The event "%s" has been not dispatched', $eventName));
    }
}
