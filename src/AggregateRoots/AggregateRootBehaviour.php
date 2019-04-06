<?php


namespace Spatie\EventProjector\AggregateRoots;


use Spatie\EventProjector\DomainEvent;

trait AggregateRootBehaviour
{
    /** @var array */
    protected $recordedEvents = [];

    /** @var string */
    protected $uuid;

    public function recordThat(DomainEvent $domainEvent)
    {
        $this->recordedEvents[] = $domainEvent;
    }

    public function getRecordedEvents(): array
    {
        $recordedEvents = $this->recordedEvents;

        $this->recordedEvents = [];

        return $recordedEvents;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }
}