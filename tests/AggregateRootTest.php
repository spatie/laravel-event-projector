<?php

namespace Spatie\EventProjector\Tests;

use Illuminate\Support\Facades\Mail;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Tests\TestClasses\AggregateRoots\AccountAggregateRoot;
use Spatie\EventProjector\Tests\TestClasses\AggregateRoots\DomainEvents\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\AggregateRoots\Mailable\MoneyAddedMailable;
use Spatie\EventProjector\Tests\TestClasses\FakeUuid;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;

final class AggregateRootTest extends TestCase
{
    /** @test */
    public function persisting_an_aggregate_root_will_persist_all_events_it_recorded()
    {
        $uuid = FakeUuid::generate();

        $aggregateRoot = AccountAggregateRoot::retrieve($uuid);
        $aggregateRoot->addMoney(100);
        $aggregateRoot->persist();

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
        $uuid = FakeUuid::generate();

        /** @var \Spatie\EventProjector\Tests\TestClasses\AggregateRoots\AccountAggregateRoot $aggregateRoot */
        $aggregateRoot = AccountAggregateRoot::retrieve($uuid);

        $aggregateRoot->addMoney(100);
        $aggregateRoot->addMoney(100);
        $aggregateRoot->addMoney(100);

        $aggregateRoot->persist();

        $aggregateRoot = AccountAggregateRoot::retrieve($uuid);

        $this->assertEquals(300, $aggregateRoot->balance);
    }

    /** @test */
    public function it_will_register_and_call_projectors()
    {
        $uuid = FakeUuid::generate();

        $aggregateRoot = AccountAggregateRoot::retrieve($uuid);
        $aggregateRoot->addMoney(123);
        $aggregateRoot->persist();

        $accounts = Account::get();
        $this->assertCount(1, $accounts);

        $account = Account::first();
        $this->assertEquals(123, $account->amount);
        $this->assertEquals($uuid, $account->uuid);
    }

    /** @test */
    public function it_will_register_and_call_reactors()
    {
        Mail::fake();

        $uuid = FakeUuid::generate();

        $aggregateRoot = AccountAggregateRoot::retrieve($uuid);
        $aggregateRoot->addMoney(123);
        $aggregateRoot->persist();

        Mail::assertSent(MoneyAddedMailable::class, function(MoneyAddedMailable $mailable) use ($uuid) {
            $this->assertEquals($uuid, $mailable->uuid);
            $this->assertEquals(123, $mailable->amount);

            return true;
        });
    }
}

