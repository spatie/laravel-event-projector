<?php

namespace Spatie\EventProjector\Tests\TestClasses\AggregateRoots\Mailable;

use Illuminate\Mail\Mailable;

final class MoneyAddedMailable extends Mailable
{
    /** @var int */
    public $amount;

    /** @var string */
    public $uuid;

    public function __construct(int $amount, string $uuid)
    {
        $this->amount = $amount;

        $this->uuid = $uuid;
    }

    public function build()
    {
        return $this->html('');
    }
}
