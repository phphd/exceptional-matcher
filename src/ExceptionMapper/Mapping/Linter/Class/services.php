<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Linter;

use PhPhD\ExceptionMapper\Mapping\Blueprint\Compiler\ExceptionMappingCompiler;
use PhPhD\ExceptionMapper\Mapping\Blueprint\Compiler\Log\SpyLogger;
use PhPhD\ExceptionMapper\Mapping\Linter\Class\ClassMappingLinter;
use PhPhD\ExceptionMapper\Mapping\Linter\Class\CompilationDefectCollector;
use PhPhD\ExceptionMapper\Mapping\Linter\Report\LintReport;
use PhPhD\ExceptionMapper\Mapping\Object\Blueprint\ObjectExceptionMappingBlueprint;
use PhPhD\ExceptionMapper\Mapping\Object\Blueprint\Registry\Compiler\CompilingObjectExceptionMappingBlueprintRegistry;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\PropertyExceptionMappingBlueprint;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(MappingLinter::class.'<class-string,'.LintReport::class.'>', ClassMappingLinter::class)
        ->arg(
            '$printRegistry',
            inline_service(CompilingObjectExceptionMappingBlueprintRegistry::class)
                ->arg('$compiler', service(ClassMappingLinter::class.'::$objectCompiler')),
        )->arg('$objectCompiler', service(ClassMappingLinter::class.'::$objectCompiler'))
        ->arg(
            '$propertyCompiler',
            inline_service(ExceptionMappingCompiler::class)
                ->factory([
                    service(ExceptionMappingCompiler::class.'<'.ReflectionProperty::class.','.PropertyExceptionMappingBlueprint::class.'>'),
                    'reportingTo',
                ])->args([service(CompilationDefectCollector::class.'::$compilationLogger')]),
        )->arg('$compilationDefectCollector', service(CompilationDefectCollector::class))
        ->tag(MappingLinter::class, ['id' => 'class-string'])
    ;

    $services // Lint-mode compiler - collects and reports mapping problems
        ->set(ClassMappingLinter::class.'::$objectCompiler', ExceptionMappingCompiler::class)
        ->factory([
            service(ExceptionMappingCompiler::class.'<'.ReflectionClass::class.','.ObjectExceptionMappingBlueprint::class.'>'),
            'reportingTo',
        ])->args([service(CompilationDefectCollector::class.'::$compilationLogger')])
    ;

    $services
        ->set($id = CompilationDefectCollector::class, $id)
        ->arg('$compilationLogger', service(CompilationDefectCollector::class.'::$compilationLogger'))
    ;

    // Linter's own logger to collect compilation exceptions
    $services->set(CompilationDefectCollector::class.'::$compilationLogger', SpyLogger::class);
};
