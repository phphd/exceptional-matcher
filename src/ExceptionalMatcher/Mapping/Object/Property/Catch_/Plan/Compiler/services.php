<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\CatchExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Autoload\ConstantsClassLoader;
use PhPhD\ExceptionalMatcher\Mapping\Plan\Compiler\ExceptionMappingCompiler;
use ReflectionAttribute;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Throwable;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(
            ExceptionMappingCompiler::class.'<'.ReflectionAttribute::class.','.CatchExceptionMappingPlan::class.'>',
            CatchExceptionMappingCompiler::class,
        )
        ->arg('$matchConditionCompiler', service(MatchConditionCompiler::class.'<'.Throwable::class.'>'))
        ->arg('$exceptionFormatterRegistry', tagged_locator(MatchedExceptionFormatter::class, 'id'))
        // #[Catch_] compilation requires mapping constants loaded
        ->configurator(service(ConstantsClassLoader::class))
    ;
};
