---
title: Tracking handled events
weight: 2
---

The package keeps track of which events were already passed to which projectors. When replaying events it will never pass an event to a projector that already handled it.

The `projector_statuses` table contains info on each projector's status. It contains these fields:

- `name`: The fully qualified class name of your projector.
- `stream`: When the projector uses event streams, the `projector_statuses` will contain multiple rows for a projector. `stream` contains the name of the stream this row applies to.
- `last_processed_event_id`: The id of the last event that was handled by this projector.

## Naming projectors

By default the fully qualified class name of the projector will get used in the `name` column of the `projector_statuses` table. You can customize that name by putting a `$name` property on your projector.

If you haven't set a `$name` on your projector, and if you'd change the fully qualified class name of your projector, you should manually update the `name` of the corresponding record in the `projector_statuses` table.

## Listing projector statuses

You can list all projectors and their status with this artisan command:

```bash
php artisan event-projector:list
```

Here's some example output:
![output of list command](/images/event-projector/list-command.png)

*It pains us, too, that the right side of the table isn't placed correctly. This is probably caused by the usage of an emoji character in the table. We hope that this little bug will get solved soon in Symfony*

The `Up to date` column will contain a green checkmark if the last processed id of that projector is equal to the latest (and greatest) id in the `stored_events` table.

## When to replay events

We'll only pass an event to a projector if its `id` is equal to the `last_processed_event_id` of that projector + 1. If the event id is lower than that we will not the pass the event to the projector.  When this happens we'll also fire the `Spatie\EventProjector\EventsProjectorDidNotHandlePriorEvents` event. It contains two public properties:

- `$projector`: An instance of `Spatie\EventProjector\Projectors\Projector`.
- `$storedEvent`: An instance of `\Spatie\EventProjector\Models\StoredEvent`. You can get to the event that was fired like this `$storedEvent->event`.

 To get a projector back up to date you should [replay events](/laravel-event-projector/v1/replaying-events/replaying-events).
