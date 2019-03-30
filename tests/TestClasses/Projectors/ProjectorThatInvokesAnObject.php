<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;

final class AddMoneyToAccount
{
    public function __invoke(MoneyAdded $event)
    {
        $event->account->addMoney($event->amount);
    }
}

final class ProjectorThatInvokesAnObject implements Projector
{
    use ProjectsEvents;

    protected $handlesEvents = [
        MoneyAdded::class => AddMoneyToAccount::class,
    ];
}
