<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object;

use PhPhD\ExceptionalMatcher\Mapping\Autoload\ConstantsAutoloadingCompilerPass;
use PhPhD\ExceptionalMatcher\Mapping\Autoload\ConstantsClassLoader;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Compiler\ObjectExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry\CompilingObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry\MemoizingObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry\ObjectExceptionMappingPlanRegistry;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service_closure;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(ObjectExceptionMappingPlanRegistry::class, CompilingObjectExceptionMappingPlanRegistry::class)
        ->args([service(ObjectExceptionMappingPlanCompiler::class)])
        ->configurator(service(ConstantsClassLoader::class));

    $services
        ->set(MemoizingObjectExceptionMappingPlanRegistry::class, MemoizingObjectExceptionMappingPlanRegistry::class)
        ->decorate(ObjectExceptionMappingPlanRegistry::class)
        ->args([
            service('.inner'),
        ])
    ;
};
