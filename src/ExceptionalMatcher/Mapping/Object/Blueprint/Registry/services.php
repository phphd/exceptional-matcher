<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry;

use PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\ExceptionMappingCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\ObjectExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\Compiler\CompilingObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\Memory\MemoizingObjectExceptionMappingPlanRegistry;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set($id = MemoizingObjectExceptionMappingPlanRegistry::class, $id)
        ->decorate(ObjectExceptionMappingPlanRegistry::class)
        ->args([service('.inner')])
        ->tag('kernel.reset', ['method' => 'clear'])
    ;

    $services
        ->set(ObjectExceptionMappingPlanRegistry::class, CompilingObjectExceptionMappingPlanRegistry::class)
        ->args([service(ExceptionMappingCompiler::class.'<'.ReflectionClass::class.','.ObjectExceptionMappingPlan::class.'>')])
    ;
};
