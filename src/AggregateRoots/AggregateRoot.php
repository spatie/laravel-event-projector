<?php

namespace Spatie\EventProjector\AggregateRoots;

use Spatie\EventProjector\DomainEvent;

interface AggregateRoot
{
    public function recordThat(DomainEvent $event);

    public function getRecordedEvents(): array;

    public function setUuid(string $uuid);

    public function getUuid(): string;
}
