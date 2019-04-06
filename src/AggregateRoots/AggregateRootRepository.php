<?php

namespace Spatie\EventProjector\AggregateRoots;

use Illuminate\Support\Str;
use Spatie\EventProjector\DomainEvent;
use Spatie\EventProjector\Models\StoredEvent;

abstract class AggregateRootRepository
{
    public function instanciateAggregateRoot(): AggregateRoot
    {
        return app($this->aggregateRoot);
    }

    public function retrieve(string $uuid)
    {
        $aggreageRoot = $this->instanciateAggregateRoot();

        $this->reconstituteFromEvents($uuid, $aggreageRoot);

        return $aggreageRoot;
    }

    private function reconstituteFromEvents(string $uuid, AggregateRoot $aggreageRoot): void
    {
        $aggreageRoot->setUuid($uuid);

        StoredEvent::uuid($uuid)->each(function (StoredEvent $storedEvent) use ($aggreageRoot) {
            $classBaseName = class_basename($storedEvent);

            $camelCasesBaseName = Str::camel($classBaseName);

            $applyingMethodName = "apply{$camelCasesBaseName}";

            $event = $storedEvent->event;

            $aggreageRoot->$applyingMethodName($event, $storedEvent);
        });
    }

    public function persist(AggregateRoot $aggregateRoot)
    {
        collect($aggregateRoot->getRecordedEvents())->each(function(DomainEvent $newDomainEvent) {
            event($newDomainEvent);
        });
    }
}

