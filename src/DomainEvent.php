<?php

namespace Spatie\EventProjector;

interface DomainEvent
{
    public function getUuid(): string;
}
