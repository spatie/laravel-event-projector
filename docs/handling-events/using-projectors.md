---
title: Using projectors
weight: 2
---

A projector is a class that listens for events that were stored. When it hears an event that it is interested in, it can perform some work.

## Creating projectors

Let's create a projector. You can perform this artisan command to create a projector in `app\Projectors`:

```php
php artisan make:projector AccountBalanceProjector
```

## Registering projectors

Projectors can be registered in the `projectors` key of the `event-projectors` config file.

Alternatively, you can add them to the `Projectionist`. This can be done anywhere, but typically you would do this in a ServiceProvider of your own.

```php
namespace App\Providers;

use App\Projectors\AccountBalanceProjector;
use Illuminate\Support\ServiceProvider;
use Spatie\EventProjector\Facades\Projectionist;

class EventProjectorServiceProvider extends ServiceProvider
{
    public function register()
    {
        // adding a single projector
        Projectionist::addProjector(AccountBalanceProjector::class);

        // you can also add multiple projectors in one go
        Projectionist::addProjectors([
            AnotherProjector::class,
            YetAnotherProjector::class,
        ]);
    }
}
```

## Using projectors

This is the contents of a class created by the artisan command mentioned in the section above.

```php
namespace App\Projectors;

use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;

class AccountBalanceProjector implements Projector
{
    use ProjectsEvents;

    /*
     * Here you can specify which event should trigger which method.
     */
    protected $handlesEvents = [
        // EventHappened::class => 'onEventHappened',
    ];

    /*
    public function onEventHappened(EventHappended $event)
    {

    }
    */
}
```

The `$handlesEvents` property is an array which has event class names as keys and method names as values. Whenever an event is fired that matches one of the keys in `$handlesEvents` the corresponding method will be fired. You can name your methods however you like.

Here's an example where we listen for a `MoneyAdded` event:

```php
namespace App\Projectors;

use App\Account;
use App\Events\MoneyAdded;
use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;

class AccountBalanceProjector implements Projector
{
    use ProjectsEvents;

    /*
     * Here you can specify which event should trigger which method.
     */
    protected $handlesEvents = [
        MoneyAdded::class => 'onMoneyAdded',
    ];

    public function onMoneyAdded(MoneyAdded $event)
    {
        // do some work
    }
}
```

When the package needs to call the projector, it will use the container to create that projector so you may inject any dependencies in the constructor. In fact, all methods specified in `$handlesEvent` can make use of method injection, so you can resolve any dependencies you need in those methods as well. Any variable in the method signature with the name `$event` will receive the event you're listening for.

## Using default event handling method names

In the example above the events are mapped to methods on the projector using the `$handlesEvents` property.

```php
// in a projector

// ...

protected $handlesEvents = [
    MoneyAdded::class => 'onMoneyAdded',
];
```

You can write this a little shorter. Just put the class name of an event in that array. The package will infer the method name to be called. It will assume that there is a method called `on` followed by the name of the event. Here's an example:

```php
// in a projector

// ...

protected $handlesEvents = [
    /*
     * If this event is passed to the projector, the `onMoneyAdded` method will be called.
     */ 
    MoneyAdded::class,
];
```

## Using a class as an event handler

Instead of letting a method on a projector handle an event you can use a dedicated class.

```php
// in a projector

// ...

protected $handlesEvents = [
    /*
     * If this event is passed to the projector, the `AddMoneyToAccount` class will be called.
     */ 
    MoneyAdded::class => AddMoneyToAccount::class,
];
```

Here's an example implementation of `AddMoneyToAccount`:

```php
use App\Events\MoneyAdded;

class AddMoneyToAccount
{
    public function __invoke(MoneyAdded $event)
    {
        $event->account->addMoney($event->amount);
    }
}
```

## Performing some work before and after replaying events

When [replaying events](/laravel-event-projector/v1/replaying-events/replaying-events) projectors will get called, too.

If your projector has a `onStartingEventReplay` method, it will get called right before the first event will be replayed. This can be handy to clean up any data your projector writes to. Here's an example where we truncate the `accounts` table before replaying events:

```php
namespace App\Projectors;

use App\Account;

// ...

class AccountBalanceProjector implements Projector
{
    use ProjectsEvents;

    // ...

    public function onStartingEventReplay()
    {
        Account::truncate();
    }
}
```

After all events are replayed, the `onFinishedEventReplay` method will be called, should your projector have one.

## Naming projectors

When tracking [the handled events of a projector](/laravel-event-projector/v1/replaying-events/tracking-handled-events) the package will by default use the fully qualified name of your projector. You can customize that name by putting a `$name` property on your projector.

Here's an example:

```php
namespace App\Projectors;

use App\Account;

// ...

class AccountBalanceProjector implements Projector
{
    use ProjectsEvents;

    public $name = 'accountBalanceProjector';

    // ...
}
```

## Using streams

Projectors can handle multiple streams on events. Read the using event streams section to learn why you'll need stream and how to use them.
