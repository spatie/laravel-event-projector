<?php

namespace Spatie\EventProjector\Console\Concerns;

use Illuminate\Support\Collection;
use Spatie\EventProjector\Projectors\Projector;
use Spatie\EventProjector\Projectors\ProjectsEvents;

trait ReplaysEvents
{
    use ProjectsEvents;

    public function replay(Collection $projectors)
    {
        $replayCount = $this->getStoredEventClass()::count();

        if ($replayCount === 0) {
            $this->warn('There are no events to replay');

            return;
        }


        $this->comment('Replaying all events...');

        $bar = $this->output->createProgressBar($this->getStoredEventClass()::count());
        $onEventReplayed = function () use ($bar) {
            $bar->advance();
        };

        $this->projectionist->replay($projectors, 0, $onEventReplayed);

        $bar->finish();

        $this->emptyLine(2);
        $this->comment('All done!');
    }

    protected function emptyLine(int $amount = 1)
    {
        foreach (range(1, $amount) as $i) {
            $this->line('');
        }
    }

    protected function getStoredEventClass(): string
    {
        return config('event-projector.stored_event_model');
    }
}
