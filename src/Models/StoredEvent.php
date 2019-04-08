<?php

namespace Spatie\EventProjector\Models;

use Exception;
use Carbon\Carbon;
use Spatie\EventProjector\Projectionist;
use Spatie\EventProjector\ShouldBeStored;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\SchemalessAttributes\SchemalessAttributes;
use Spatie\EventProjector\Exceptions\InvalidStoredEvent;
use Spatie\EventProjector\EventSerializers\EventSerializer;

class StoredEvent extends Model
{
    public $guarded = [];

    public $timestamps = false;

    public $casts = [
        'event_properties' => 'array',
        'meta_data' => 'array',
    ];

    public static function createForEvent(ShouldBeStored $event, string $uuid = null): StoredEvent
    {
        $storedEvent = new static();
        $storedEvent->aggregate_uuid = $uuid;
        $storedEvent->event_class = get_class($event);
        $storedEvent->attributes['event_properties'] = app(EventSerializer::class)->serialize(clone $event);
        $storedEvent->meta_data = [];
        $storedEvent->created_at = Carbon::now();

        $storedEvent->save();

        return $storedEvent;
    }

    public function getEventAttribute(): ShouldBeStored
    {
        try {
            $event = app(EventSerializer::class)->deserialize(
                $this->event_class,
                $this->getOriginal('event_properties')
            );
        } catch (Exception $exception) {
            throw InvalidStoredEvent::couldNotUnserializeEvent($this, $exception);
        }

        return $event;
    }

    public function scopeStartingFrom(Builder $query, int $storedEventId): void
    {
        $query->where('id', '>=', $storedEventId);
    }

    public function scopeUuid(Builder $query, string $uuid): void
    {
        $query->where('aggregate_uuid', $uuid);
    }

    public function getMetaDataAttribute(): SchemalessAttributes
    {
        return SchemalessAttributes::createForModel($this, 'meta_data');
    }

    public function scopeWithMetaDataAttributes(): Builder
    {
        return SchemalessAttributes::scopeWithSchemalessAttributes('meta_data');
    }

    public function storeEvents(array $events, string $uuid = null): void
    {
        collect($events)
            ->map(function (ShouldBeStored $domainEvent) use ($uuid) {
                $storedEvent = static::createForEvent($domainEvent, $uuid);

                return [$domainEvent, $storedEvent];
            })
            ->eachSpread(function (ShouldBeStored $event, StoredEvent $storedEvent) {
                $this->getProjectionist()->handleImmediately($storedEvent);

                if (method_exists($event, 'tags')) {
                    $tags = $event->tags();
                }

                $storedEventJob = $this->getStoredEventJob()::createForEvent($storedEvent, $tags ?? []);

                dispatch($storedEventJob->onQueue($this->config['queue']));
            });
    }

    public function storeEvent(ShouldBeStored $event, string $uuid = null): void
    {
        $this->storeEvents([$event], $uuid);
    }

    private function getStoredEventJob(): string
    {
        return config('event-projector.stored_event_job');
    }

    private function getProjectionist(): Projectionist
    {
        return app(Projectionist::class);
    }
}
