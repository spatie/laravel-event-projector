---
title: Making sure events get handled in the right order
---

By default all events are handled in a synchronous manner. This means that if you fire off an event in a request, all projectors will get called in the same request.

If you have a lot of concurrent requests that fire off events there's a chance that projectors will fall behind. For a more detailed look at this problem and the solution provided by the package read the section [on using event streams](/laravel-event-projector/v1/basic-usage/using-event-streams).

## Handling events in a queue

A queue can be used to guarantee that all events get passed to projectors in the right order. If you want a projector to handle events in a queue, you should let your projector implement the `Spatie\EventProjector\Projectors\QueuedProjector` interface instead of the the normal `Spatie\EventProjector\Projectors\Projector`. This interface merely hints to the `Projectionist` that the event handling should happen in a queued manner.

A useful rule of thumb is that if your projectors aren't producing data that is consumed in the same request as the events are fired, you should let your projector implement `QueuedProjector`.

You can set the name of the queue connection in the `queue` key of the `event-projector` config file.  You should make sure that the queue will process only one job at a time.

In a local environment, where events have a very low chance of getting fired concurrently, it's probably ok to just use the `sync` driver.
