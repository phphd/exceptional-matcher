<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\Compiler;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\ExceptionMappingCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\ObjectExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\PropertyExceptionMappingPlan;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator, ContainerBuilder $container): void {
    $services = $configurator->services();

    $planCompiler = $services
        ->set(
            ExceptionMappingCompiler::class.'<'.ReflectionClass::class.','.ObjectExceptionMappingPlan::class.'>',
            ObjectExceptionMappingCompiler::class,
        )
        ->args([
            service(ExceptionMappingCompiler::class.'<'.ReflectionProperty::class.','.PropertyExceptionMappingPlan::class.'>'),
        ])
    ;

    if ($container->hasParameter('kernel.debug') && false === $container->getParameter('kernel.debug')) {
        $planCompiler
            ->call('reportingTo', [service('logger')->ignoreOnInvalid()], returnsClone: true)
            ->tag('monolog.logger', ['channel' => PhdExceptionalMatcherExtension::LOGGER_CHANNEL])
        ;
    }
};
