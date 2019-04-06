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

    public function recordThat(DomainEvent $domainEvent)
    {
        $this->recordedEvents[] = $domainEvent;
    }

    public function recordedEvents(): array
    {
        $recordedEvents = $this->recordedEvents;

        $this->recordedEvents = [];

        return $recordedEvents;
    }

    private function getUuid(): string
    {
        return $this->uuid;
    }

    private function setUuid(string $uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    public static function retrieve(string $uuid)
    {
        return (new static())
            ->setUuid($uuid)
            ->reconstituteFromEvents();
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

    public function persist()
    {
        collect($this->recordedEvents())->each(function(DomainEvent $newDomainEvent) {
            app(Projectionist::class)->storeEvent($newDomainEvent, $this->getUuid());
        });

        return $this;
    }
}
