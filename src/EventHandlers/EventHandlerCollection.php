<?php

namespace Spatie\EventProjector\EventHandlers;

use Illuminate\Support\Collection;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Exceptions\InvalidEventHandler;

final class EventHandlerCollection
{
    /** @var \Illuminate\Support\Collection */
    private $eventHandlers;

    public function __construct($eventHandlers = [])
    {
        $this->eventHandlers = collect();

        foreach ($eventHandlers as $eventHandler) {
            $this->add($eventHandler);
        }
    }

    public function add($eventHandler): void
    {
        if (is_string($eventHandler)) {
            if ($this->eventHandlers->has($eventHandler)) {
                return;
            }

            $eventHandler = app($eventHandler);
        }

        if (! $eventHandler instanceof EventHandler) {
            throw InvalidEventHandler::notAnEventHandler($eventHandler);
        }

        $className = get_class($eventHandler);

        if (! $this->eventHandlers->has($className)) {
            $this->eventHandlers[$className] = $eventHandler;
        }
    }

    public function all(): Collection
    {
        return $this->eventHandlers;
    }

    public function forEvent(StoredEvent $storedEvent): Collection
    {
        return $this->eventHandlers->filter(function (EventHandler $eventHandler) use ($storedEvent) {
            return in_array($storedEvent->event_class, $eventHandler->handles(), true);
        });
    }

    public function call(string $method)
    {
        $this->eventHandlers
            ->filter(function (EventHandler $eventHandler) use ($method) {
                return method_exists($eventHandler, $method);
            })
            ->each(function (EventHandler $eventHandler) use ($method) {
                return app()->call([$eventHandler, $method]);
            });
    }

    public function removeAll(): void
    {
        $this->eventHandlers = collect();
    }

    public function remove(array $eventHandlerClassNames): void
    {
        $this->eventHandlers = $this->eventHandlers
            ->reject(function (EventHandler $eventHandler) use ($eventHandlerClassNames) {
                return in_array(get_class($eventHandler), $eventHandlerClassNames);
            });
    }
}
