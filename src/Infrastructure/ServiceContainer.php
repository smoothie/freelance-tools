<?php

declare(strict_types=1);

namespace App\Infrastructure;

use App\Application\Application;
use App\Application\ApplicationInterface;
use App\Application\EventDispatcher;
use App\Application\EventDispatcherWithSubscribers;
use Assert\Assert;

abstract class ServiceContainer
{
    protected ?EventDispatcher $eventDispatcher = null;

    protected ?ApplicationInterface $application = null;

    public function eventDispatcher(): EventDispatcher
    {
        if ($this->eventDispatcher === null) {
            $this->eventDispatcher = new EventDispatcherWithSubscribers();

            $this->registerEventSubscribers($this->eventDispatcher);
        }

        Assert::that($this->eventDispatcher)->isInstanceOf(EventDispatcher::class);

        return $this->eventDispatcher;
    }

    public function application(): ApplicationInterface
    {
        if ($this->application === null) {
            $this->application = new Application();
        }

        return $this->application;
    }

    protected function registerEventSubscribers(EventDispatcherWithSubscribers $eventDispatcher): void
    {
        //        $eventDispatcher->subscribeToSpecificEvent(
        //            MemberRequestedAccess::class,
        //            [$this->accessPolicy(), 'whenMemberRequestedAccess']
        //        );
    }
}
