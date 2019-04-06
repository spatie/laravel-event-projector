<?php

namespace Spatie\EventProjector\Tests\TestClasses\AggregateRoots\Messages;

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

