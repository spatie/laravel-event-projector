<?php

namespace Spatie\EventProjector\Tests\TestClasses\Events;

use Illuminate\Support\Str;
use Spatie\EventProjector\DomainEvent;
use Spatie\EventProjector\Tests\TestClasses\FakeUuid;

final class EventWithoutSerializedModels implements DomainEvent
{
    /** @var string */
    public $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
