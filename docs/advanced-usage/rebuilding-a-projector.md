---
title: Rebuilding a projector
---

When replaying events, a projector will only receive events it has not handled yet. If you want to build up the state of a projector again from scratch, you can rebuild it.

## Preparing your projector

In order to make your projector rebuildable, you have to add a `resetState` to it. In this method you'll have to perform the work necessary to clean up the state of your projector. When rebuilding the projector, we will call this method. If for instance, your projector is backed by an Eloquent model, you can truncate that model.

```php
namespace App\Projectors;

use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;

class ResettableProjector implements Projector
{
    use ProjectsEvents;

    public function resetState()
    {
        // do the work to clean up the state of this projector...
    }
}
```

## Rebuild your projector

You can rebuild a projector by using this artisan command. In this example we are going to rebuild the `AccountBalanceProjector`:

```bash
php artisan event-projector:rebuild App\\Projectors\\AccountBalanceProjector
```

This will call the `resetState` method on the projector and replay all events.

You can also rebuild multiple projectors in one go:

```bash
php artisan event-projector:rebuild App\\Projectors\\AccountBalanceProjector App\Projectors\AnotherProjector
```

If you have [named your projector](https://docs.spatie.be/laravel-event-projector/v1/handling-events/using-projectors#naming-projectors) you can use the projector name instead of the fully qualified class name.

If you want to rebuild all projectors simply don't pass a projector name. You'll need to confirm before all projectors will actually be rebuild.

```bash
php artisan event-projector:rebuild 
```

## Resetting your projector

You can also remove all state from a projector but not replay events with this command:

```bash
php artisan event-projector:reset App\\Projectors\\AccountBalanceProjector
```

You can also reset multiple projectors in one go:

```bash
php artisan event-projector:reset App\\Projectors\\AccountBalanceProjector App\Projectors\AnotherProjector
```

## Resetting your projector via code

You can also reset a projector with code:

```php
use Spatie\EventProjector\Facades\Projectionist;

//...

$projector = Projectionist::getProjector($projectorClassOrName);

$projector->reset();
```
