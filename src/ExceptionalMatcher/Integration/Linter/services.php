<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter;

use PhPhD\ExceptionalMatcher\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Integration\Linter\Defect\MappingDefectCollector;
use PhPhD\ExceptionalMatcher\Mapping\Autoload\ConstantsAutoloadingCompilerPass;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Compiler\ObjectExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\_Compiler\PropertyExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch\_Plan\_Compiler\CatchExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Throwable;

use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service_closure;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services->set(MappingDefectCollector::class, MappingDefectCollector::class);

    // Lint-mode plan registry: it keeps compiling past a broken mapping and reports every one it drops.
    $services
        ->set('phd_exceptional_matcher.linter.plan_registry', ObjectExceptionMappingPlanRegistry::class)
        ->args([
            inline_service(ObjectExceptionMappingPlanCompiler::class)
                ->args([
                    inline_service(PropertyExceptionMappingPlanCompiler::class)
                        ->args([
                            inline_service(CatchExceptionMappingPlanCompiler::class)
                                ->args([
                                    service(MatchConditionCompiler::class.'<'.Throwable::class.'>'),
                                    true, // $throwOnFailure: the property level enriches the failure with the property it belongs to
                                    null,
                                ]),
                            false, // $throwOnFailure
                            service(MappingDefectCollector::class),
                        ]),
                    false, // $throwOnFailure
                    service(MappingDefectCollector::class),
                ]),
            service_closure(ConstantsAutoloadingCompilerPass::AUTOLOADER_ID),
        ])
    ;

    $services
        ->set(MappingLinter::class, MappingLinter::class)
        ->public()
        ->args([
            service('phd_exceptional_matcher.linter.plan_registry'),
            tagged_locator(MatchedExceptionFormatter::class, 'id'),
            service(MappingDefectCollector::class),
        ])
    ;
};
