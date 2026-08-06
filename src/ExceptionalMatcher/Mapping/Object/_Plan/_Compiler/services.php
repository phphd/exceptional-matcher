<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object;

use Closure;
use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Compiler\ObjectExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\_Compiler\PropertyExceptionMappingPlanCompiler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator, ContainerBuilder $container): void {
    $services = $configurator->services();

    /** @var Closure(class-string,bool=):((bool|class-string)) $lazy */
    $lazy = $container->get('phd_exceptional_matcher.lazy_proxy');

    $services
        ->set(ObjectExceptionMappingPlanCompiler::class, ObjectExceptionMappingPlanCompiler::class)
        ->args([
            service(PropertyExceptionMappingPlanCompiler::class),
            true, // $throwOnFailure
            service('logger')
                ->nullOnInvalid(),
        ])
        ->tag('monolog.logger', ['channel' => PhdExceptionalMatcherExtension::LOGGER_CHANNEL])
        // the property compiler it is built with needs the registry this compiler serves: being lazy is what
        // lets the registry be built first
        ->lazy($lazy(ExceptionMappingPlanCompiler::class, true /* $required */))
    ;
};
