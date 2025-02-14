<?php

declare(strict_types=1);

namespace App\Tests\Acceptance;

use App\Infrastructure\ServiceContainer;
use Test\Acceptance\EventDispatcherSpy;

final class ServiceContainerForAcceptanceTesting extends ServiceContainer
{
    private ?EventDispatcherSpy $eventDispatcherSpy = null;

//    protected function registerEventSubscribers(EventDispatcherWithSubscribers $eventDispatcher): void
//    {
//        parent::registerEventSubscribers($eventDispatcher);
//
//        $eventDispatcher->subscribeToAllEvents([$this->eventDispatcherSpy(), 'notify']);
//
//        // Test-specific listeners:
//        //        $eventDispatcher->subscribeToSpecificEvent(
//        //            SessionWasPlanned::class,
//        //            [$this->sessions(), 'whenSessionWasPlanned']
//        //        );
//    }
//
//    //    protected function purchaseRepository(): PurchaseRepository
//    //    {
//    //        if ($this->purchaseRepository === null) {
//    //            $this->purchaseRepository = new PurchaseRepositoryInMemory();
//    //        }
//    //
//    //        return $this->purchaseRepository;
//    //    }
//
//    public function eventDispatcherSpy(): EventDispatcherSpy
//    {
//        if ($this->eventDispatcherSpy === null) {
//            $this->eventDispatcherSpy = new EventDispatcherSpy();
//        }
//
//        return $this->eventDispatcherSpy;
//    }
}
