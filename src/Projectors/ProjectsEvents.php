<?php

namespace Spatie\EventProjector\Projectors;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\EventProjector\Models\StoredEvent;
use Spatie\EventProjector\Models\ProjectorStatus;
use Spatie\EventProjector\EventHandlers\HandlesEvents;
use Spatie\EventProjector\Exceptions\CouldNotResetProjector;

trait ProjectsEvents
{
    use HandlesEvents;

    public function getName(): string
    {
        if (isset($this->name)) {
            return $this->name;
        }

        return get_class($this);
    }

    public function rememberReceivedEvent(StoredEvent $storedEvent)
    {
        foreach ($this->getEventStreamFullNames($storedEvent) as $streamName) {
            $status = $this->getStatus($streamName);

            $status->rememberLastProcessedEvent($storedEvent, $this);

            $streams = $this->getEventStreams($storedEvent);

            if ($streams->isEmpty()) {
                $lastStoredEvent = $this->getStoredEventClass()::query()
                    ->whereIn('event_class', $this->handles())
                    ->orderBy('id', 'desc')
                    ->first();

                $lastStoredEventId = (int) optional($lastStoredEvent)->id ?? 0;

                $status = $this->getStatus();

                $status->last_processed_event_id === $lastStoredEventId
                    ? $status->markAsReceivedAllEvents()
                    : $status->markasAsNotReceivedAllEvents();

                return;
            }

            foreach ($streams as $streamName => $streamValue) {
                $streamFullName = "{$streamName}-{$streamValue}";
                $whereJsonClause = '\'$."'.str_replace('.', '"."', $streamName).'"\'';

                $lastStoredEvent = $this->getStoredEventClass()::query()
                    ->whereIn('event_class', $this->handles())
                    ->whereRaw("JSON_EXTRACT(`event_properties`, {$whereJsonClause}) = ?", [$streamValue])
                    ->orderBy('id', 'desc')
                    ->first();

                $lastStoredEventId = (int) optional($lastStoredEvent)->id ?? 0;

                $status = $this->getStatus($streamFullName);

                $status->last_processed_event_id === $lastStoredEventId
                    ? $status->markAsReceivedAllEvents()
                    : $status->markasAsNotReceivedAllEvents();
            }
        }
    }

    public function hasAlreadyReceivedEvent(StoredEvent $storedEvent): bool
    {
        foreach ($this->getEventStreamFullNames($storedEvent) as $streamFullName) {
            $status = $this->getStatus($streamFullName);

            $lastProcessedEventId = (int) optional($status)->last_processed_event_id ?? 0;

            if ($storedEvent->id <= $lastProcessedEventId) {
                return true;
            }
        }

        return false;
    }

    public function hasReceivedAllPriorEvents(StoredEvent $storedEvent): bool
    {
        $streams = $this->getEventStreams($storedEvent);

        if ($streams->isEmpty()) {
            $lastStoredEvent = $this->getStoredEventClass()::query()
                ->whereIn('event_class', $this->handles())
                ->where('id', '<', $storedEvent->id)
                ->orderBy('id', 'desc')
                ->first();

            $lastStoredEventId = (int) optional($lastStoredEvent)->id ?? 0;

            $status = $this->getStatus();

            $lastProcessedEventId = (int) $status->last_processed_event_id ?? 0;

            if ($lastStoredEventId !== $lastProcessedEventId) {
                return false;
            }

            return true;
        }

        foreach ($streams as $streamName => $streamValue) {
            $streamFullName = "{$streamName}-{$streamValue}";
            $whereJsonClause = '\'$."'.str_replace('.', '"."', $streamName).'"\'';

            $lastStoredEvent = $this->getStoredEventClass()::query()
                ->whereIn('event_class', $this->handles())
                ->where('id', '<', $storedEvent->id)
                ->whereRaw("JSON_EXTRACT(`event_properties`, {$whereJsonClause}) = ?", [$streamValue])
                ->orderBy('id', 'desc')
                ->first();

            $lastStoredEventId = (int) optional($lastStoredEvent)->id ?? 0;

            $status = $this->getStatus($streamFullName);
            $lastProcessedEventId = (int) $status->last_processed_event_id ?? 0;

            if ($lastStoredEventId !== $lastProcessedEventId) {
                return false;
            }
        }

        return true;
    }

    public function hasReceivedAllEvents(): bool
    {
        return $this->getProjectorStatusClass()::hasReceivedAllEvents($this);
    }

    public function markAsNotUpToDate(StoredEvent $storedEvent)
    {
        foreach ($this->getEventStreamFullNames($storedEvent) as $streamName) {
            $status = $this->getStatus($streamName);

            $status->markasAsNotReceivedAllEvents();
        }
    }

    public function getLastProcessedEventId(): int
    {
        return $this->getStatus()->last_processed_event_id ?? 0;
    }

    public function lastEventProcessedAt(): Carbon
    {
        return $this->getStatus()->updated_at;
    }

    public function reset()
    {
        if (! method_exists($this, 'resetState')) {
            throw CouldNotResetProjector::doesNotHaveResetStateMethod($this);
        }

        $this->resetState();

        $this->getAllStatuses()->each->delete();
    }

    public function shouldBeCalledImmediately(): bool
    {
        return ! $this instanceof QueuedProjector;
    }

    protected function getEventStreams(StoredEvent $storedEvent): Collection
    {
        $streams = method_exists($this, 'streamEventsBy')
            ? $this->streamEventsBy($storedEvent)
            : [];

        return collect(array_wrap($streams))
            ->mapWithKeys(function ($streamValue, $streamName) use ($storedEvent) {
                if (is_numeric($streamName)) {
                    $streamName = $streamValue;

                    $streamValue = array_get($storedEvent->event_properties, $streamName);
                }

                return [$streamName => $streamValue];
            });
    }

    protected function getEventStreamFullNames(StoredEvent $storedEvent): array
    {
        $streamFullNames = $this->getEventStreams($storedEvent)
            ->map(function ($streamValue, $streamName) {
                return "{$streamName}-{$streamValue}";
            })
            ->toArray();

        if (count($streamFullNames) === 0) {
            $streamFullNames = ['main'];
        }

        return $streamFullNames;
    }

    protected function getStatus(string $stream = 'main'): ProjectorStatus
    {
        return $this->getProjectorStatusClass()::getForProjector($this, $stream);
    }

    protected function getAllStatuses(): Collection
    {
        return $this->getProjectorStatusClass()::getAllForProjector($this);
    }

    protected function getStoredEventClass(): string
    {
        return config('event-projector.stored_event_model');
    }

    protected function getProjectorStatusClass(): string
    {
        return config('event-projector.projector_status_model');
    }
}
