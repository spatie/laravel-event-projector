<?php

namespace Spatie\EventProjector\Tests;

use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Tests\TestClasses\AggregateRoots\AggregateRootRepository;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAddedEvent;
use Spatie\EventProjector\Tests\TestClasses\FakeUuid;

final class AggregateRootRepositoryTest extends TestCase
{
    /** @test */
    public function persisting_an_aggregate_root_will_persist_all_events_it_recorded()
    {
        $repository = new AggregateRootRepository();

        $uuid = FakeUuid::generate();

        /** @var \Spatie\EventProjector\Tests\TestClasses\AggregateRoots\AggregateRoot $aggregateRoot */
        $aggregateRoot = $repository->retrieve($uuid);

        $aggregateRoot->addMoney(100);

        $repository->persist($aggregateRoot);

        $storedEvents  = StoredEvent::get();
        $this->assertCount(1, $storedEvents);

        $storedEvent = $storedEvents->first();
        $this->assertEquals($uuid, $storedEvent->uuid);

        $event = $storedEvent->event;
        $this->assertInstanceOf(MoneyAddedEvent::class, $event);
        $this->assertEquals($uuid, $event->uuid);
        $this->assertEquals(100, $event->amount);
    }
}

