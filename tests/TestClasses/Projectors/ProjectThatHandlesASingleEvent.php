<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;

final class ProjectThatHandlesASingleEvent implements Projector
{
    use ProjectsEvents;

    public $handleEvent = MoneyAdded::class;

    public function __invoke(MoneyAdded $event)
    {
        $event->account->addMoney($event->amount);
    }
}