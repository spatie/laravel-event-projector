<?php

namespace Spatie\EventProjector\AggregateRoots;

use Illuminate\Support\Str;
use Spatie\EventProjector\DomainEvent;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Projectionist;

abstract class AggregateRootRepository
{
    /** @var \Spatie\EventProjector\Projectionist */
    private $projectionist;

    public function __construct(Projectionist $projectionist)
    {
        $this->projectionist = $projectionist;
    }

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
            $classBaseName = class_basename($storedEvent->event_class);

            $camelCasesBaseName = ucfirst(Str::camel($classBaseName));

            $applyingMethodName = "apply{$camelCasesBaseName}";

            $event = $storedEvent->event;

            $aggreageRoot->$applyingMethodName($event, $storedEvent);
        });
    }

    public function persist(AggregateRoot $aggregateRoot)
    {
        collect($aggregateRoot->recordedEvents())->each(function(DomainEvent $newDomainEvent) use ($aggregateRoot) {
            $this->projectionist->storeEvent($newDomainEvent, $aggregateRoot->getUuid());
        });
    }
}

