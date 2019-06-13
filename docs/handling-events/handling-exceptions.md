---
title: Handling exceptions
weight: 4
---

The `event-projector` config file has a key, `catch_exceptions`, that determines what will happen should a projector or reactor throw an exception. If this setting is set to `false`, exceptions will not be caught and your app will come to a grinding halt.

If `catch_exceptions` is set to `true`, and an projector or reactor throws an exception, all other projectors and reactors will still get called. The `Projectionist` will catch all exceptions and fire the `EventHandlerFailedHandlingEvent`. That event contains these public properties:

- `eventHandler`: The projector or reactor that could not handle the event.
- `storedEvent`: The instance of `Spatie\EventProjector\Models\StoredEvent` that could not be handled.
- `exception`: The exception thrown by the `EventHandler`.

It will also call the `handleException` method on the projector or reactor that threw the exception. It will receive the thrown error as the first argument. If you throw an exception in `handleException`, the `Projectionist` will not catch it and your php process will fail.

## Getting projectors up to date again

The `Projectionist` [keeps track](https://docs.spatie.be/laravel-event-projector/v1/replaying-events/tracking-handled-events) of which events are handled by which projectors. If a projector throws an exception the `EventHandler` will conclude that the given event was not handled.

Because the projector is not up to date anymore, new events for this projector will not be passed to it. To get your projector back up to date you should, after you've fixed the cause of the exception, [replay events](https://docs.spatie.be/laravel-event-projector/v1/replaying-events/replaying-events).
