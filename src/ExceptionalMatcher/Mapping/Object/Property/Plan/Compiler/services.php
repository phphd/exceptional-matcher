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

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator, ContainerBuilder $container): void {
    $services = $configurator->services();

    /** @var Closure(class-string):((bool|class-string)) $lazy */
    $lazy = $container->get('phd_exceptional_matcher.lazy_proxy');

    $services
        ->set(
            ExceptionMappingPlanCompiler::class.'<'.ReflectionProperty::class.','.PropertyExceptionMappingPlan::class.'>',
            PropertyExceptionMappingPlanCompiler::class,
        )->args([
            service(ExceptionMappingPlanCompiler::class.'<'.ReflectionAttribute::class.','.CatchExceptionMappingPlan::class.'>'),
            service(ObjectExceptionMappingPlanRegistry::class),
        ])
        // Must be lazy, because it injects plan registry
        ->lazy($lazy(ExceptionMappingPlanCompiler::class))
    ;
};
