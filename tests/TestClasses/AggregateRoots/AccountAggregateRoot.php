<?php

namespace Spatie\EventProjector\Tests\TestClasses\AggregateRoots;

use Spatie\EventProjector\AggregateRoots\AggregateRoot;
use Spatie\EventProjector\AggregateRoots\AggregateRootBehaviour;
use Spatie\EventProjector\Tests\TestClasses\AggregateRoots\DomainEvents\MoneyAdded;

final class AccountAggregateRoot implements AggregateRoot
{
    use AggregateRootBehaviour;

    public $balance = 0;

    public function addMoney(int $amount)
    {
        $this->recordThat(new MoneyAdded($amount));
    }

    public function applyMoneyAdded(MoneyAdded $event)
    {
        $this->balance += $event->amount;
    }
}

