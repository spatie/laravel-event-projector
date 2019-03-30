<?php

namespace Spatie\EventProjector\Console\Snapshots;

use Illuminate\Support\Facades\Artisan;
use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Facades\Projectionist;
use Spatie\EventProjector\Models\ProjectorStatus;
use Spatie\EventProjector\Tests\TestClasses\Projectors\ResettableProjector;

class ResetCommandTest extends TestCase
{
    /** @test */
    public function it_can_reset_a_projector()
    {
        Projectionist::addProjector(ResettableProjector::class);

        Artisan::call('event-projector:reset', [
            'projector' => [ResettableProjector::class],
        ]);

        $this->assertSeeInConsoleOutput('Projector(s) reset');
    }
}
