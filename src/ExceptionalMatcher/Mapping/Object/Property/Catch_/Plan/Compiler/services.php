<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\CatchExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Autoload\ConstantsClassLoader;
use PhPhD\ExceptionalMatcher\Mapping\Plan\Compiler\ExceptionMappingPlanCompiler;
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
