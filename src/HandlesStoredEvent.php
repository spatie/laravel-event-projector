<?php


namespace Spatie\EventProjector;


/**
 * Trait HandlesStoredEvent
 *
 * @package Spatie\EventProjector
 */
trait HandlesStoredEvent
{

    /**
     * @param \Spatie\EventProjector\Projectionist $projectionist
     */
    public function handle(Projectionist $projectionist)
    {
        $projectionist->handle($this->storedEvent);
    }

    /**
     * @return array
     */
    public function tags(): array
    {
        if (empty($this->tags))
        {
            return [
                $this->storedEvent['event_class'],
            ];
        }

        return $this->tags;
    }
}