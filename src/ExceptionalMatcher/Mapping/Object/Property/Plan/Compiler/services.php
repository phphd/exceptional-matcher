<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Plan\Compiler;

use Closure;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\CatchExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Plan\PropertyExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Plan\Compiler\ExceptionMappingPlanCompiler;
use ReflectionAttribute;
use ReflectionProperty;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service_closure;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service_locator;

return static function (ContainerConfigurator $configurator, ContainerBuilder $container): void {
    $services = $configurator->services();

    /** @var Closure(class-string):((bool|class-string)) $hintLazy */
    $hintLazy = $container->get('phd_exceptional_matcher.hint_lazy_proxy');

    $services
        ->set(
            ExceptionMappingPlanCompiler::class.'<'.ReflectionProperty::class.','.PropertyExceptionMappingPlan::class.'>',
            PropertyExceptionMappingPlanCompiler::class,
        )->args([
            service(ExceptionMappingPlanCompiler::class.'<'.ReflectionAttribute::class.','.CatchExceptionMappingPlan::class.'>'),
            // #[Autowire(lazy: ObjectExceptionMappingPlanRegistry::class)]
            service(PropertyExceptionMappingPlanCompiler::class.'::$planRegistry'),
        ])
        // Better if lazy, since ObjectPlanCompiler doesn't always reach it.
        ->lazy($hintLazy(ExceptionMappingPlanCompiler::class))
    ;

    // Making property lazy, lest it creates circular reference:
    $services
        ->set(
            PropertyExceptionMappingPlanCompiler::class.'::$planRegistry',
            ObjectExceptionMappingPlanRegistry::class
        )->lazy()
        ->factory('current')
        ->args([[service(ObjectExceptionMappingPlanRegistry::class)]]);
};
