<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Blueprint\Compiler;

use PhPhD\ExceptionMapper\Mapping\Blueprint\Compiler\ExceptionMappingCompiler;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Blueprint\CatchExceptionMappingBlueprint;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Blueprint\Compiler\Autoload\ConstantsClassLoader;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Blueprint\MatchConditionCompiler;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\ExceptionMatchFormatter;
use ReflectionAttribute;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Throwable;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(
            ExceptionMappingCompiler::class.'<'.ReflectionAttribute::class.','.CatchExceptionMappingBlueprint::class.'>',
            CatchExceptionMappingCompiler::class,
        )
        ->arg('$matchConditionCompiler', service(MatchConditionCompiler::class.'<'.Throwable::class.'>'))
        ->arg('$exceptionFormatterRegistry', tagged_locator(ExceptionMatchFormatter::class, 'id'))
        // #[Catch_] compilation requires mapping constants loaded
        ->configurator(service(ConstantsClassLoader::class))
    ;
};
