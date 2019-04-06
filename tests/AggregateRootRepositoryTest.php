<?php

namespace Spatie\EventProjector\Tests;

use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Tests\TestClasses\AggregateRoots\AccountAggregateRootRepository;
use Spatie\EventProjector\Tests\TestClasses\AggregateRoots\DomainEvents\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\FakeUuid;

final class AggregateRootRepositoryTest extends TestCase
{
    /** @test */
    public function persisting_an_aggregate_root_will_persist_all_events_it_recorded()
    {
        $repository =  app(AccountAggregateRootRepository::class);

        $uuid = FakeUuid::generate();

        /** @var \Spatie\EventProjector\Tests\TestClasses\AggregateRoots\AccountAggregateRoot $aggregateRoot */
        $aggregateRoot = $repository->retrieve($uuid);

        $aggregateRoot->addMoney(100);

        $repository->persist($aggregateRoot);

        $storedEvents  = StoredEvent::get();
        $this->assertCount(1, $storedEvents);

        $storedEvent = $storedEvents->first();
        $this->assertEquals($uuid, $storedEvent->uuid);

        $event = $storedEvent->event;
        $this->assertInstanceOf(MoneyAdded::class, $event);
        $this->assertEquals(100, $event->amount);
    }

    /** @test */
    public function when_retrieving_an_aggregate_root_all_events_will_be_replayed_to_it()
    {
        $repository =  app(AccountAggregateRootRepository::class);

        $uuid = FakeUuid::generate();

        /** @var \Spatie\EventProjector\Tests\TestClasses\AggregateRoots\AccountAggregateRoot $aggregateRoot */
        $aggregateRoot = $repository->retrieve($uuid);

        $aggregateRoot->addMoney(100);
        $aggregateRoot->addMoney(100);
        $aggregateRoot->addMoney(100);

        $repository->persist($aggregateRoot);

        $aggregateRoot = $repository->retrieve($uuid);

        $this->assertEquals(300, $aggregateRoot->balance);
    }
}

