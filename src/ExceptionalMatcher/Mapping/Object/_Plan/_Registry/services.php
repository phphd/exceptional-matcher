<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object;

use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Compiler\ObjectExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry\CompilingObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry\MemoizingObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry\ObjectExceptionMappingPlanRegistry;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator, ContainerBuilder $container): void {
    $services = $configurator->services();

    $services
        ->set(MemoizingObjectExceptionMappingPlanRegistry::class, MemoizingObjectExceptionMappingPlanRegistry::class)
        ->decorate(ObjectExceptionMappingPlanRegistry::class)
        ->args([service('.inner')])
        ->tag('kernel.reset', ['method' => 'clear'])
    ;

    $services
        ->set(ObjectExceptionMappingPlanRegistry::class, CompilingObjectExceptionMappingPlanRegistry::class)
        ->args([service(ObjectExceptionMappingPlanCompiler::class)]);
};
