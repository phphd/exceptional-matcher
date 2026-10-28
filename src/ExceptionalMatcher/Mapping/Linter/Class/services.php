<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter;

use PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\ExceptionMappingCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\Log\SpyLogger;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Class\ClassMappingLinter;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Class\CompilationDefectCollector;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\LintReport;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\ObjectExceptionMappingBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\Compiler\CompilingObjectExceptionMappingBlueprintRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Blueprint\PropertyExceptionMappingBlueprint;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(MappingLinter::class.'<class-string,'.LintReport::class.'>', ClassMappingLinter::class)
        ->arg('$printRegistry',
            inline_service(CompilingObjectExceptionMappingBlueprintRegistry::class)
                ->arg('$compiler',
                    // Lint-mode compiler - it collects and reports mapping problems
                    inline_service(ExceptionMappingCompiler::class)
                        ->factory([
                            service(ExceptionMappingCompiler::class.'<'.ReflectionClass::class.','.ObjectExceptionMappingBlueprint::class.'>'),
                            'reportingTo',
                        ])->args([service(CompilationDefectCollector::class.'::$compilationLogger')]),
                ),
        )->arg('$propertyMappingCompiler',
            inline_service(ExceptionMappingCompiler::class)
                ->factory([
                    service(ExceptionMappingCompiler::class.'<'.ReflectionProperty::class.','.PropertyExceptionMappingBlueprint::class.'>'),
                    'reportingTo',
                ])->args([service(CompilationDefectCollector::class.'::$compilationLogger')])
        )->arg('$compilationDefectCollector', service(CompilationDefectCollector::class))
        ->tag(MappingLinter::class, ['id' => 'class-string']);

    $services
        ->set($id = CompilationDefectCollector::class, $id)
        ->arg('$compilationLogger', service(CompilationDefectCollector::class.'::$compilationLogger'));

    // Linter's own logger to collect compilation exceptions
    $services->set(CompilationDefectCollector::class.'::$compilationLogger', SpyLogger::class);
};
