<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAddedEvent;

final class AddMoneyToAccount
{
    public function __invoke(MoneyAddedEvent $event)
    {
        $event->account->addMoney($event->amount);
    }
}

final class ProjectorThatInvokesAnObject implements Projector
{
    use ProjectsEvents;

    protected $handlesEvents = [
        MoneyAddedEvent::class => AddMoneyToAccount::class,
    ];
}
