<?php

namespace Spatie\EventProjector;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Spatie\EventProjector\Models\StoredEvent;

abstract class AggregateRoot
{
    /** @var string */
    private $uuid;

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
            [$this->getStoredEventModel(), 'storeMany'],
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

    private function getStoredEventModel(): string
    {
        return config('event-projector.stored_event_model');
    }
}
