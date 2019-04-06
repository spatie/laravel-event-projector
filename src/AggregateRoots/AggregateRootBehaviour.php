<?php

namespace Spatie\EventProjector\AggregateRoots;

use Spatie\EventProjector\DomainEvent;

trait AggregateRootBehaviour
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

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }
}