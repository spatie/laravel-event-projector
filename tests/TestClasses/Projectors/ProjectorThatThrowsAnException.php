<?php

namespace Spatie\EventProjector\Tests\TestClasses\Projectors;

use Exception;
use Spatie\EventProjector\Projectors\QueuedProjector;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAddedEvent;

class ProjectorThatThrowsAnException extends ProjectorWithoutHandlesEvents implements QueuedProjector
{
    public function onMoneyAdded(MoneyAddedEvent $event)
    {
        throw new Exception('Computer says no.');
    }
}
