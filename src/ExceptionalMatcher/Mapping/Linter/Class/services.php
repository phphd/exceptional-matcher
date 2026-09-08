<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter;

use PhPhD\ExceptionalMatcher\Mapping\Linter\Class\ClassMappingLinter;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Class\MappingDefectCollector;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\LintReport;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\ObjectExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Compiler\CompilingObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Plan\PropertyExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Plan\Compiler\ExceptionMappingPlanCompiler;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(MappingLinter::class.'<class-string,'.LintReport::class.'>', ClassMappingLinter::class)
        ->public()
        ->args([
            // Lint-mode plan registry: it keeps compiling past a broken mapping
            inline_service(CompilingObjectExceptionMappingPlanRegistry::class)
                ->args([
                    // Lint-mode compiler - it collects and reports mapping problems
                    inline_service(ExceptionMappingPlanCompiler::class)
                        ->factory([service(ExceptionMappingPlanCompiler::class.'<'.ReflectionClass::class.','.ObjectExceptionMappingPlan::class.'>'), 'reportingTo'])
                        ->args([service(MappingDefectCollector::class)]),
                ]),
            inline_service(ExceptionMappingPlanCompiler::class)
                ->factory([service(ExceptionMappingPlanCompiler::class.'<'.ReflectionProperty::class.','.PropertyExceptionMappingPlan::class.'>'), 'reportingTo'])
                ->args([service(MappingDefectCollector::class)]),
            service(MappingDefectCollector::class),
        ])
        ->tag(MappingLinter::class, ['id' => 'class-string'])
    ;

    $services->set(MappingDefectCollector::class, MappingDefectCollector::class);
};
