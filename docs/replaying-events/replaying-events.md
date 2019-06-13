---
title: Replaying events
weight: 1
---

All [events](/laravel-event-projector/v1/handling-events/preparing-events) that implement `Spatie\EventProjector\ShouldBeStored` will be [serialized](https://docs.spatie.be/laravel-event-projector/v1/advanced-usage/using-your-own-event-serializer) and stored in the `stored_events` table. After your app has been doing its work for a while the `stored_events` table will probably contain some events.

 When creating a new [projector](/laravel-event-projector/v1/handling-events/using-projectors) or [reactor](/laravel-event-projector/v1/handling-events/using-reactors) you'll want to feed all stored events to that new projector or reactor. We call this process replaying events.

 Events can be replayed to [all projectors that were added to the projectionist](/laravel-event-projector/v1/handling-events/using-reactors) with this artisan command:

 ```bash
 php artisan event-projector:replay
 ```

The package [keeps track of which events were already passed to a projector](https://docs.spatie.be/laravel-event-projector/v1/replaying-events/tracking-handled-events). It will never pass an event that a projector already handled. So it's generally safe to replay events. Only projectors that didn't handle all events yet will get called. Projectors that already handled all past events will not be called.

 You can also handpick a projectors by using the `--projector` option. All stored events will be passed only to that projector.

 ```bash
  php artisan event-projector:replay --projector=App\\Projectors\\AccountBalanceProjector
 ```

 You can use the projector option multiple times:

  ```bash
   php artisan event-projector:replay --projector=App\\Projectors\\AccountBalanceProjector --projector=App\\Projectors\\AnotherProjector
  ```

## Detecting event replays

If your projector contains an `onStartingEventReplay` method, we'll call it right before the first event is replayed.

If it contains an `onFinishedEventReplay` method, we'll call it right after all events have been replayed.

You can also detect the start and end of event replay by listening for the `Spatie\EventProjector\Events\StartingEventReplay` and `Spatie\EventProjector\Events\FinishedEventReplay` events.

Though, under normal circumstances, you don't need to know this, you can detect if events are currently being replayed like this:

```php
Spatie\EventProjector\Facades\Projectionist::isReplayingEvents(); // returns a boolean
```

## Models with timestamps

When using models with timestamps, it is important to keep in mind that the projector will create or update these models when replaying and the timestamps will not correspond to the event's original timestamps. This will probably not be behavior you intended. To work around this you can use the stored event's timestamps:

```php
public function onAccountCreated(StoredEvent $storedEvent, AccountCreated $event) {
        Account::create(array_merge($event->accountAttributes, ['created_at' => $storedEvent->created_at, 'updated_at' => $storedEvent->created_at]));
}
```

## What about reactors?

Reactors are used to handle side effects, like sending mails and such. You'll only want reactors to do their work when an event is originally fired. You don't want to send out mails when replaying events. That's why reactors will never get called when replaying events.  
