<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter;

use PhPhD\ExceptionalMatcher\Integration\Linter\Defect\MappingDefectCollector;
use PhPhD\ExceptionalMatcher\Mapping\ento\Plan\Compiler\ExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\ento\Plan\Compiler\ObjectExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\ento\Plan\ObjectExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\ento\Plan\Registry\CompilingObjectExceptionMappingPlanRegistry;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(MappingLinter::class, MappingLinter::class)
        ->public()
        ->args([
            // Lint-mode plan registry: it keeps compiling past a broken mapping
            inline_service(CompilingObjectExceptionMappingPlanRegistry::class)
                ->args([
                    // Lint-mode compiler - it collects and reports mapping problems
                    inline_service(ObjectExceptionMappingPlanCompiler::class)
                        ->factory([service(ExceptionMappingPlanCompiler::class.'<'.ReflectionClass::class.','.ObjectExceptionMappingPlan::class.'>'), 'reportingTo'])
                        ->args([service(MappingDefectCollector::class)]),
                ]),
            service(MappingDefectCollector::class),
        ])
    ;

    $services->set(MappingDefectCollector::class, MappingDefectCollector::class);
};
