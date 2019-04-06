<?php

namespace Spatie\EventProjector\Tests\TestClasses\AggregateRoots;

use Spatie\EventProjector\AggregateRoots\AggregateRootRepository;

final class AccountAggregateRootRepository extends AggregateRootRepository
{
    public $aggregateRoot = AccountAggregateRoot::class;
}

