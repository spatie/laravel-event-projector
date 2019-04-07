<?php

namespace Spatie\EventProjector\Tests\TestClasses\AggregateRoots\DomainEvents;

use Spatie\EventProjector\DomainEvent;

final class MoneyAdded implements DomainEvent
{
    /** @var int */
    public $amount;

    public function __construct(int $amount)
    {
        $this->amount = $amount;
    }
}
