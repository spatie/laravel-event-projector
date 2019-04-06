<?php

namespace Spatie\EventProjector\Tests\TestClasses\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Spatie\EventProjector\DomainEvent;
use Spatie\EventProjector\Tests\TestClasses\FakeUuid;
use Spatie\EventProjector\Tests\TestClasses\Models\Account;

final class MoneyAddedEvent implements DomainEvent
{
    use SerializesModels;

    /** @var string */
    public $uuid;

    /** @var int */
    public $amount;

    public function __construct(string $uuid, int $amount)
    {
        $this->uuid = $uuid;

        $this->amount = $amount;
    }

    public function tags(): array
    {
        return [
            'Account:'.$this->uuid,
            self::class,
        ];
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }
}
