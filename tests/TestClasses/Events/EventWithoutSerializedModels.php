<?php

namespace Spatie\EventProjector\Tests\TestClasses\Events;

use Spatie\EventProjector\DomainEvent;

class EventWithoutSerializedModels implements DomainEvent
{
    /** @var string */
    public $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
