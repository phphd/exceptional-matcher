<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry;

use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\ObjectExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Plan\Compiler\ExceptionMappingPlanCompiler;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(MemoizingObjectExceptionMappingPlanRegistry::class, MemoizingObjectExceptionMappingPlanRegistry::class)
        ->decorate(ObjectExceptionMappingPlanRegistry::class)
        ->args([service('.inner')])
        ->tag('kernel.reset', ['method' => 'clear'])
    ;

    $services
        ->set(ObjectExceptionMappingPlanRegistry::class, CompilingObjectExceptionMappingPlanRegistry::class)
        ->args([service(ExceptionMappingPlanCompiler::class.'<'.ReflectionClass::class.','.ObjectExceptionMappingPlan::class.'>')])
    ;
};
