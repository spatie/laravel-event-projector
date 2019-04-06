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
        $this->registerEventHandlers();

        collect($this->recordedEvents())->each(function(DomainEvent $newDomainEvent) {
            $this->getProjectionist()->storeEvent($newDomainEvent, $this->uuid);
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

    private function reconstituteFromEvents(): AggregateRoot
    {
        StoredEvent::uuid($this->uuid)->each(function (StoredEvent $storedEvent) {
            $classBaseName = class_basename($storedEvent->event_class);

            $camelCasedBaseName = ucfirst(Str::camel($classBaseName));

            $applyingMethodName = "apply{$camelCasedBaseName}";

            $event = $storedEvent->event;

            $this->$applyingMethodName($event, $storedEvent);
        });

        return $this;
    }

    private function getProjectionist(): Projectionist
    {
        return app(Projectionist::class);
    }

    private function registerEventHandlers()
    {

        $this->getProjectionist()
            ->addProjectors($this->projectors ?? [])
            ->addReactors($this->reactors ?? []);


        static::$eventHandlersRegistered = true;
    }
}
