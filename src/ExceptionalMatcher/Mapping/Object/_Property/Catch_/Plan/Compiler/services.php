<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Plan\Compiler;

use PhPhD\ExceptionalMatcher\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Mapping\_Plan\_Compiler\ExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Autoload\ConstantsClassLoader;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\_Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Plan\CatchExceptionMappingPlan;
use ReflectionAttribute;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Throwable;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(
            ExceptionMappingPlanCompiler::class.'<'.ReflectionAttribute::class.','.CatchExceptionMappingPlan::class.'>',
            CatchExceptionMappingPlanCompiler::class,
        )
        ->args([
            service(MatchConditionCompiler::class.'<'.Throwable::class.'>'),
            tagged_locator(MatchedExceptionFormatter::class, 'id'),
        ])
        // #[Catch_] compilation requires mapping constants to be loaded
        ->configurator(service(ConstantsClassLoader::class))
    ;
};
