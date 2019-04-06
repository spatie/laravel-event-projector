<?php

namespace Spatie\EventProjector;

use Illuminate\Support\Str;
use Spatie\EventProjector\Models\StoredEvent;

abstract class AggregateRoot
{
    /** @var array */
    private $recordedEvents = [];

    /** @var string */
    private $uuid;

    public static function retrieve(string $uuid): AggregateRoot
    {
        $aggregateRoot =  (new static());

        $aggregateRoot->uuid = $uuid;

        return $aggregateRoot->reconstituteFromEvents();
    }

    public function persist(): AggregateRoot
    {
        collect($this->recordedEvents())->each(function(DomainEvent $newDomainEvent) {
            app(Projectionist::class)->storeEvent($newDomainEvent, $this->uuid);
        });

        return $this;
    }

    public function recordThat(DomainEvent $domainEvent): AggregateRoot
    {
        $this->recordedEvents[] = $domainEvent;

        return $this;
    }

    private function recordedEvents(): array
    {
        $recordedEvents = $this->recordedEvents;

        $this->recordedEvents = [];

        return $recordedEvents;
    }

    private function reconstituteFromEvents()
    {
        StoredEvent::uuid($this->uuid)->each(function (StoredEvent $storedEvent) {
            $classBaseName = class_basename($storedEvent->event_class);

            $camelCasesBaseName = ucfirst(Str::camel($classBaseName));

            $applyingMethodName = "apply{$camelCasesBaseName}";

            $event = $storedEvent->event;

            $this->$applyingMethodName($event, $storedEvent);
        });

        return $this;
    }
}
