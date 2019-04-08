<?php

namespace Spatie\EventProjector;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\EventHandlers\EventHandler;
use Spatie\EventProjector\Events\FinishedEventReplay;
use Spatie\EventProjector\Events\StartingEventReplay;
use Spatie\EventProjector\Exceptions\InvalidEventHandler;
use Spatie\EventProjector\EventHandlers\EventHandlerCollection;
use Spatie\EventProjector\Events\EventHandlerFailedHandlingEvent;

final class Projectionist
{
    /** @var array */
    private $config;

    /** @var \Spatie\EventProjector\EventHandlers\EventHandlerCollection */
    private $projectors;

    /** @var \Spatie\EventProjector\EventHandlers\EventHandlerCollection */
    private $reactors;

    /** @var bool */
    private $isProjecting = false;

    /** @var bool */
    private $isReplaying = false;

    public function __construct(array $config = [])
    {
        $this->projectors = new EventHandlerCollection();

        $this->reactors = new EventHandlerCollection();

        $this->config = $config;
    }

    public function addProjector($projector): Projectionist
    {
        if (is_string($projector)) {
            $projector = app($projector);
        }

        if (! $projector instanceof Projector) {
            throw InvalidEventHandler::notAProjector($projector);
        }

        $this->projectors->add($projector);

        return $this;
    }

    public function removeEventHandlers($eventHandlers = null): Projectionist
    {
        if (is_null($eventHandlers)) {
            $this->projectors->removeAll();

            $this->reactors->removeAll();
        }

        $eventHandlers = Arr::wrap($eventHandlers);

        $this->projectors->remove($eventHandlers);

        $this->reactors->remove($eventHandlers);

        return $this;
    }

    public function addProjectors(array $projectors): Projectionist
    {
        foreach ($projectors as $projector) {
            $this->addProjector($projector);
        }

        return $this;
    }

    public function getProjectors(): Collection
    {
        return $this->projectors->all();
    }

    public function getProjector(string $name): ?Projector
    {
        return $this->projectors->all()->first(function (Projector $projector) use ($name) {
            return $projector->getName() === $name;
        });
    }

    public function addReactor($reactor): Projectionist
    {
        $this->reactors->add($reactor);

        return $this;
    }

    public function addReactors(array $reactors): Projectionist
    {
        foreach ($reactors as $reactor) {
            $this->addReactor($reactor);
        }

        return $this;
    }

    public function getReactors(): Collection
    {
        return $this->reactors->all();
    }

    public function storeEvents(array $events, string $uuid = null): void
    {
        collect($events)
            ->map(function (ShouldBeStored $domainEvent) use ($uuid) {
                $storedEvent = $this->getStoredEventClass()::createForEvent($domainEvent, $uuid);

                return [$domainEvent, $storedEvent];
            })
            ->eachSpread(function (ShouldBeStored $event, StoredEvent $storedEvent) {
                $this->handleImmediately($storedEvent);

                if (method_exists($event, 'tags')) {
                    $tags = $event->tags();
                }

                $storedEventJob = $this->getStoredEventJob()::createForEvent($storedEvent, $tags ?? []);

                dispatch($storedEventJob->onQueue($this->config['queue']));
            });
    }

    public function storeEvent(ShouldBeStored $event, string $uuid = null): void
    {
        $this->storeEvents([$event], $uuid);
    }

    public function handle(StoredEvent $storedEvent): void
    {
        $projectors = $this->projectors
            ->forEvent($storedEvent)
            ->reject(function (Projector $projector) {
                return $projector->shouldBeCalledImmediately();
            });

        $this->applyStoredEventToProjectors(
            $storedEvent,
            $projectors
        );

        $this->applyStoredEventToReactors(
            $storedEvent,
            $this->reactors->forEvent($storedEvent)
        );
    }

    public function handleImmediately(StoredEvent $storedEvent): void
    {
        $projectors = $this->projectors
            ->forEvent($storedEvent)
            ->filter(function (Projector $projector) {
                return $projector->shouldBeCalledImmediately();
            });

        $this->applyStoredEventToProjectors($storedEvent, $projectors);
    }

    public function isProjecting(): bool
    {
        return $this->isProjecting;
    }

    private function applyStoredEventToProjectors(StoredEvent $storedEvent, Collection $projectors): void
    {
        $this->isProjecting = true;

        foreach ($projectors as $projector) {
            $this->callEventHandler($projector, $storedEvent);
        }

        $this->isProjecting = false;
    }

    private function applyStoredEventToReactors(StoredEvent $storedEvent, Collection $reactors): void
    {
        foreach ($reactors as $reactor) {
            $this->callEventHandler($reactor, $storedEvent);
        }
    }

    private function callEventHandler(EventHandler $eventHandler, StoredEvent $storedEvent): bool
    {
        try {
            $eventHandler->handle($storedEvent);
        } catch (Exception $exception) {
            if (! $this->config['catch_exceptions']) {
                throw $exception;
            }

            $eventHandler->handleException($exception);

            event(new EventHandlerFailedHandlingEvent($eventHandler, $storedEvent, $exception));

            return false;
        }

        return true;
    }

    public function isReplaying(): bool
    {
        return $this->isReplaying;
    }

    public function replay(
        Collection $projectors,
        int $startingFromEventId = 0,
        callable $onEventReplayed = null
    ): void {
        $projectors = new EventHandlerCollection($projectors);

        $this->isReplaying = true;

        if ($startingFromEventId === 0) {
            $projectors->all()->each(function (Projector $projector) {
                if (method_exists($projector, 'resetState')) {
                    $projector->resetState();
                }
            });
        }

        event(new StartingEventReplay($projectors->all()));

        $projectors->call('onStartingEventReplay');

        $this->getStoredEventClass()::query()
            ->startingFrom($startingFromEventId ?? 0)
            ->chunk($this->config['replay_chunk_size'], function (Collection $storedEvents) use ($projectors, $onEventReplayed) {
                $storedEvents->each(function (StoredEvent $storedEvent) use ($projectors, $onEventReplayed) {
                    $this->applyStoredEventToProjectors(
                        $storedEvent,
                        $projectors->forEvent($storedEvent)
                    );

                    if ($onEventReplayed) {
                        $onEventReplayed($storedEvent);
                    }
                });
            });

        $this->isReplaying = false;

        event(new FinishedEventReplay());

        $projectors->call('onFinishedEventReplay');
    }

    private function getStoredEventClass(): string
    {
        return config('event-projector.stored_event_model');
    }

    private function getStoredEventJob(): string
    {
        return config('event-projector.stored_event_job');
    }
}
