<?php

namespace Spatie\EventProjector;

use Illuminate\Support\Str;
use Spatie\EventProjector\Models\StoredEvent;

abstract class AggregateRoot
{
    /** @var string */
    private $aggregate_uuid;

    /** @var array */
    private $recordedEvents = [];

    public static function retrieve(string $uuid): AggregateRoot
    {
        $aggregateRoot = (new static());

        $aggregateRoot->aggregate_uuid = $uuid;

        return $aggregateRoot->reconstituteFromEvents();
    }

    public function recordThat(ShouldBeStored $domainEvent): AggregateRoot
    {
        $this->recordedEvents[] = $domainEvent;

        $this->apply($domainEvent);

        return $this;
    }

    public function persist(): AggregateRoot
    {
        call_user_func(
            [config('event-projector.stored_event_model'), 'storeMany'],
            $this->getAndClearRecoredEvents(),
            $this->aggregate_uuid
        );

        return $this;
    }

    private function getAndClearRecoredEvents(): array
    {
        $recordedEvents = $this->recordedEvents;

        $this->recordedEvents = [];

        return $recordedEvents;
    }

    private function reconstituteFromEvents(): AggregateRoot
    {
        StoredEvent::uuid($this->aggregate_uuid)->each(function (StoredEvent $storedEvent) {
            $this->apply($storedEvent->event);
        });

        return $this;
    }

    private function apply(ShouldBeStored $event): void
    {
        $classBaseName = class_basename($event);

        $camelCasedBaseName = ucfirst(Str::camel($classBaseName));

        $applyingMethodName = "apply{$camelCasedBaseName}";

        if (method_exists($this, $applyingMethodName)) {
            $this->$applyingMethodName($event);
        }
    }
}
