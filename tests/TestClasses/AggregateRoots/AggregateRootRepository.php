<?php

namespace Spatie\EventProjector\Tests\TestClasses\AggregateRoots;

final class AggregateRootRepository extends \Spatie\EventProjector\AggregateRoots\AggregateRootRepository
{
    public $aggregateRoot = AggregateRoot::class;
}

