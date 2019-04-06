<?php

namespace Spatie\EventProjector\Tests\TestClasses\Events;

use Illuminate\Queue\SerializesModels;
use Spatie\EventProjector\DomainEvent;
use Spatie\EventProjector\Tests\TestClasses\FakeUuid;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;

final class MoneySubtractedEvent implements DomainEvent
{
    use SerializesModels;

    /** @var \Spatie\EventProjector\Tests\TestClasses\Models\Account */
    public $account;

    /** @var int */
    public $amount;

    public function __construct(Account $account, int $amount)
    {
        $this->account = $account;

        $this->amount = $amount;
    }

    public function getUuid(): string
    {
        return FakeUuid::generate();
    }
}
