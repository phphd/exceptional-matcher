<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter;

use PhPhD\ExceptionalMatcher\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry\ObjectExceptionMappingPlanRegistry;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(MappingLinter::class, MappingLinter::class)
        ->public()
        ->args([
            service(ObjectExceptionMappingPlanRegistry::class),
            tagged_locator(MatchedExceptionFormatter::class, 'id'),
        ])
    ;
};
