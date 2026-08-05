<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\_Compiler\PropertyExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch\_Plan\_Compiler\CatchExceptionMappingPlanCompiler;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(PropertyExceptionMappingPlanCompiler::class, PropertyExceptionMappingPlanCompiler::class)
        ->args([
            service(CatchExceptionMappingPlanCompiler::class),
            true, // $throwOnFailure
            service('logger')
                ->nullOnInvalid(),
        ])
        ->tag('monolog.logger', ['channel' => PhdExceptionalMatcherExtension::LOGGER_CHANNEL])
    ;
};
