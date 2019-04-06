<?php

namespace Spatie\EventProjector\Tests\TestClasses\AggregateRoots;

use Spatie\EventProjector\AggregateRoot;
use Spatie\EventProjector\AggregateRoots\AggregateRootBehaviour;
use Spatie\EventProjector\Tests\TestClasses\AggregateRoots\DomainEvents\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\AggregateRoots\Projectors\AccountProjector;

final class AccountAggregateRoot extends AggregateRoot
{
    protected $projectors = [
        AccountProjector::class,
    ];

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

