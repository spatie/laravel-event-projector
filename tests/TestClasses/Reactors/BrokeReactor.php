<?php

namespace Spatie\EventProjector\Tests\TestClasses\Reactors;

use Illuminate\Support\Facades\Mail;
use Spatie\EventProjector\EventHandlers\EventHandler;
use Spatie\EventProjector\EventHandlers\HandlesEvents;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneySubtractedEvent;
use Spatie\EventProjector\Tests\TestClasses\Mailables\AccountBroke;

final class BrokeReactor implements EventHandler
{
    use HandlesEvents;

    protected $handlesEvents = [
        MoneySubtractedEvent::class => 'onMoneySubtracted',
    ];

    public function onMoneySubtracted(MoneySubtractedEvent $event)
    {
        if ($event->account->isBroke()) {
            Mail::to('john@example.com')->send(new AccountBroke());
        }
    }
}
