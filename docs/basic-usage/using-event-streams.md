---
title: Using event streams
weight: 5
---

If your application receives a lot of concurrent requests, it will result in a lot of events being fired. In such a scenario there's a high chance that projectors won't process events in the right order.

Imagine that there are many requests coming in at the same time that each want to add money to 100 different accounts.

In a first request an event `AmountAdded` for the first account is fired and stored. The stored event gets id 1. In that request the projector now starts to update the amount of that account. But before that update completes, another request has already stored its own `AmountAdded` event for a second account. But because event 1 is not completed yet, the projector will not accept event 2. The projector is now out of sync: it will not accept new events until you've [replayed](/laravel-event-projector/v1/replaying-events/replaying-events) all of them.

If you think about it, the projector should perfectly be able to handle events related to the second account even if the projector hasn't handled the events that apply to the first account.

## Preparing your projector

You can make a projector understand the situation above by implementing the `streamEventsBy` methods on your projector. This function should return the name of the property to uniquely identifies the subject of your projector. The package will [track events](/laravel-event-projector/v1/replaying-events/tracking-handled-events) using that property.

Let's implement the `streamEventsBy` method on the `AccountBalanceProjector` we created in [the writing your first projector](/laravel-event-projector/v1/basic-usage/writing-your-first-projector) section. The property that unique identifies our account is `accountUuid`.

```php
class AccountBalanceProjector implements Projector
{
    use ProjectsEvents;

    // ...

    public function streamEventsBy()
    {
       return 'accountUuid';
    }
}
```

With this method implement the projector will still accept events for a given account even if it did not receive all events yet from other accounts.

It's important that you make sure all events that are passed to this projector have that `accountUuid` property.

## Using dot notation

If your events have their identifying property in an array or object you can use dot notation in the return value `streamEventsBy`. Imagine all your events have a `$account` property that contains an `Account` model. 

```php
class AccountBalanceProjector implements Projector
{
    use ProjectsEvents;

    // ...

    public function streamEventsBy()
    {
       /*
        * Let's use the id property of the account
        */
       return 'account.id';
    }
}
```
