<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Plan\Compiler;

use Closure;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\CatchExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Plan\PropertyExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Plan\Compiler\ExceptionMappingCompiler;
use ReflectionAttribute;
use ReflectionProperty;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator, ContainerBuilder $container): void {
    $services = $configurator->services();

    /** @var Closure(class-string):((bool|class-string)) $hintLazy */
    $hintLazy = $container->get('phd_exceptional_matcher.hint_lazy_proxy');

    $services
        ->set(
            ExceptionMappingCompiler::class.'<'.ReflectionProperty::class.','.PropertyExceptionMappingPlan::class.'>',
            PropertyExceptionMappingCompiler::class,
        )->args([
            service(ExceptionMappingCompiler::class.'<'.ReflectionAttribute::class.','.CatchExceptionMappingPlan::class.'>'),
            // #[Autowire(lazy: ObjectExceptionMappingPlanRegistry::class)]
            service(PropertyExceptionMappingCompiler::class.'::$planRegistry'),
        ])
        // Better if lazy, since ObjectPlanCompiler doesn't always reach it.
        ->lazy($hintLazy(ExceptionMappingCompiler::class))
    ;

    // Making the property lazy, lest it creates a circular reference:
    $services
        ->set(
            PropertyExceptionMappingCompiler::class.'::$planRegistry',
            ObjectExceptionMappingPlanRegistry::class
        )->lazy()
        ->factory('current')
        ->args([[service(ObjectExceptionMappingPlanRegistry::class)]]);
};
