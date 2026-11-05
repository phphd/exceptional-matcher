<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry;

use PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\ExceptionMappingCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\ObjectExceptionMappingBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\Compiler\CompilingObjectExceptionMappingBlueprintRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\Memory\MemoizingObjectExceptionMappingBlueprintRegistry;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set($id = MemoizingObjectExceptionMappingBlueprintRegistry::class, $id)
        ->decorate(ObjectExceptionMappingBlueprintRegistry::class)
        ->args([service('.inner')])
        ->tag('kernel.reset', ['method' => 'clear'])
    ;

    $services
        ->set(ObjectExceptionMappingBlueprintRegistry::class, CompilingObjectExceptionMappingBlueprintRegistry::class)
        ->args([service(ExceptionMappingCompiler::class.'<'.ReflectionClass::class.','.ObjectExceptionMappingBlueprint::class.'>')])
    ;
};
