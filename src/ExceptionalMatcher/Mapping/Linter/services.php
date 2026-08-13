<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter;

use PhPhD\ExceptionalMatcher\Mapping\Linter\Class\ClassNameLinter;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Defect\MappingDefectCollector;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Report\LintReport;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Compiler\ObjectExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\ObjectExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\CompilingObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Plan\Compiler\ExceptionMappingPlanCompiler;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(MappingLinter::class.'<'.'class-string,'.LintReport::class.'>', ClassNameLinter::class)
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
