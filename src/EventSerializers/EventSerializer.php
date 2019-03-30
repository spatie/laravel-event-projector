<?php

namespace Spatie\EventProjector\EventSerializers;

use Spatie\EventProjector\DomainEvent;

interface EventSerializer
{
    public function serialize(DomainEvent $event): string;

    public function deserialize(string $eventClass, string $json): DomainEvent;
}
