---
title: Using reactors
---

A reactor is a class, that much like a projector, listens for incoming events. Unlike projectors however, reactors will not get called when events are replayed. Reactors will only get called when the original event fires.

## Creating reactors

Let's create a reactor. You can perform this artisan command to create a projector in `app\Reactors`:

```php
php artisan make:reactor BigAmountAddedReactor
```

## Registering reactors

Reactors can be registered in the `reactors` key of the `event-projectors` config file.

Alternatively, they can be added to the `Projectionist`. This can be done anywhere, but typically you would do this in a ServiceProvider of your own.

```php
namespace App\Providers;

use App\Projectors\AccountBalanceProjector;
use Illuminate\Support\ServiceProvider;
use Spatie\EventProjector\Facades\Projectionist;

class EventProjectorServiceProvider extends ServiceProvider
{
    public function register()
    {
        // adding a single reactor
        Projectionist::addReactor(BigAmountAddedReactor::class);

        // you can also add multiple reactors in one go
        Projectionist::addReactors([
            AnotherReactor::class,
            YetAnotherReactor::class,
        ]);
    }
}
```

## Using reactors

This is the contents of a class created by the artisan command mentioned in the section above:

```php
namespace App\Reactors;

class BigAmountAddedReactor
{
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
namespace App\Reactors;

use App\Events\MoneyAdded;

class BigAmountAddedReactor
{
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

This reactor will be created using the container so you may inject any dependency you'd like. In fact, all methods present in `$handlesEvent` can make use of method injection, so you can resolve any dependencies you need in those methods as well. Any variable in the method signature with the name `$event` will receive the event you're listening for.

## Using default event handling method names

In the example above the events are mapped to methods on the reactor using the `$handlesEvents` property.

```php
// in a reactor

// ...

protected $handlesEvents = [
    MoneyAdded::class => 'onMoneyAdded',
];
```

You can write this a little shorter. Just put the class name of an event in that array. The package will infer the method name to be called. It will assume that there is a method called `on` followed by the name of the event. Here's an example:

```php
// in a reactor

// ...

protected $handlesEvents = [
    /*
     * If this event is passed to the reactor, the `onMoneyAdded` method will be called.
     */ 
    MoneyAdded::class,
];
```

## Using a class as an event handler

Instead of letting a method on a reactor handle an event you can use a dedicated class.

```php
// in a projector

// ...

protected $handlesEvents = [
    /*
     * If this event is passed to the projector, the `AddMoneyToAccount` class will be called.
     */ 
    MoneyAdded::class => SendMoneyAddedMail::class,
];
```

Here's an example implementation of `SendMoneyAddedMail`:

```php
use App\Events\MoneyAdded;

class SendMoneyAddedMail
{
    public function __invoke(MoneyAdded $event)
    {
        // do work to send a mail here
    }
}
