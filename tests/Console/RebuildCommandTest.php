<?php

namespace Spatie\EventProjector\Console\Snapshots;

use Illuminate\Support\Facades\Artisan;
use Spatie\EventProjector\Tests\TestCase;
use Spatie\EventProjector\Facades\Projectionist;
use Spatie\EventProjector\Models\ProjectorStatus;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;
use Spatie\EventProjector\Tests\TestClasses\Events\MoneyAdded;
use Spatie\EventProjector\Tests\TestClasses\Projectors\ResettableProjector;

class RebuildCommandTest extends TestCase
{
    /** @var \Spatie\EventProjector\Tests\TestClasses\Models\Account */
    protected $account;

    public function setUp(): void
    {
        parent::setUp();

        $this->account = Account::create();
    }

    /** @test */
    public function it_can_rebuild_a_projector()
    {
        Projectionist::addProjector(ResettableProjector::class);

        event(new MoneyAdded($this->account, 1000));

        Artisan::call('event-projector:rebuild', [
            'projector' => [ResettableProjector::class],
        ]);

        $this->assertSeeInConsoleOutput('Projector(s) rebuild!');
    }

    /** @test */
    public function it_allows_leading_slashes()
    {
        Projectionist::addProjector(ResettableProjector::class);

        Artisan::call('event-projector:rebuild', [
            'projector' => ['\\'.ResettableProjector::class],
        ]);

        $this->assertSeeInConsoleOutput('Projector(s) rebuild!');
    }
}
