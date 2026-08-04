<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Mapping\Autoload\ConstantsAutoloadingCompilerPass;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Compiler\ObjectExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\_Compiler\PropertyExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch\_Plan\_Compiler\CatchExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Throwable;

use function Symfony\Component\DependencyInjection\Loader\Configurator\abstract_arg;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(ObjectExceptionMappingPlanRegistry::class, ObjectExceptionMappingPlanRegistry::class)
        ->args([
            service(ObjectExceptionMappingPlanCompiler::class),
            abstract_arg('Injected by '.ConstantsAutoloadingCompilerPass::class),
        ])
    ;

    $services
        ->set(ObjectExceptionMappingPlanCompiler::class, ObjectExceptionMappingPlanCompiler::class)
        ->args([
            service(PropertyExceptionMappingPlanCompiler::class),
            true,
            service('logger')->nullOnInvalid(),
        ])
        ->tag('monolog.logger', ['channel' => PhdExceptionalMatcherExtension::LOGGER_CHANNEL])
    ;

    $services
        ->set(PropertyExceptionMappingPlanCompiler::class, PropertyExceptionMappingPlanCompiler::class)
        ->args([
            service(CatchExceptionMappingPlanCompiler::class),
            true,
            service('logger')->nullOnInvalid(),
        ])
        ->tag('monolog.logger', ['channel' => PhdExceptionalMatcherExtension::LOGGER_CHANNEL])
    ;

    $services
        ->set(CatchExceptionMappingPlanCompiler::class, CatchExceptionMappingPlanCompiler::class)
        ->args([
            service(MatchConditionCompiler::class.'<'.Throwable::class.'>'),
            true,
            service('logger')->nullOnInvalid(),
        ])
        ->tag('monolog.logger', ['channel' => PhdExceptionalMatcherExtension::LOGGER_CHANNEL])
    ;
};
