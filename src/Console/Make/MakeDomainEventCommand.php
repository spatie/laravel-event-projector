<?php

namespace Spatie\EventProjector\Console\Make;

use Illuminate\Console\GeneratorCommand;

class MakeDomainEventCommand extends GeneratorCommand
{
    protected $name = 'make:domain-event';

    protected $description = 'Create a domain event';

    protected $type = 'Domain event';

    protected function getStub()
    {
        return __DIR__.'/../../../stubs/domain-event.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Events';
    }
}
