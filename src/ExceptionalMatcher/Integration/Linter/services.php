<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter;

use Closure;
use PhPhD\ExceptionalMatcher\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Integration\Linter\Defect\MappingDefectCollector;
use PhPhD\ExceptionalMatcher\Mapping\Autoload\ConstantsClassLoader;
use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Compiler\ObjectExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry\CompilingObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\_Compiler\PropertyExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch\_Plan\_Compiler\CatchExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Throwable;

use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $configurator, ContainerBuilder $container): void {
    $services = $configurator->services();

    /** @var Closure(class-string,bool=):((bool|class-string)) $lazy */
    $lazy = $container->get('phd_exceptional_matcher.lazy_proxy');

    $services
        ->set(MappingLinter::class, MappingLinter::class)
        ->public()
        ->args([
            service('phd_exceptional_matcher.linter.plan_registry'),
            service(MappingDefectCollector::class),
        ])
    ;

    // Lint-mode plan registry: it keeps compiling past a broken mapping and reports every one it drops.
    $services // fixme: use normal registry
        ->set('phd_exceptional_matcher.linter.plan_registry', CompilingObjectExceptionMappingPlanRegistry::class)
        ->args([service('phd_exceptional_matcher.linter.plan_compiler')])
    ;

    $services
        ->set('phd_exceptional_matcher.linter.plan_compiler', ObjectExceptionMappingPlanCompiler::class)
        ->args([
            inline_service(PropertyExceptionMappingPlanCompiler::class)
                ->args([
                    inline_service(CatchExceptionMappingPlanCompiler::class)
                        ->args([
                            service(MatchConditionCompiler::class.'<'.Throwable::class.'>'),
                            tagged_locator(MatchedExceptionFormatter::class, 'id'),
                            true, // $throwOnFailure: the property level enriches the failure with the property it belongs to
                            null,
                        ])->configurator(service(ConstantsClassLoader::class)),
                    // nested plans go through the lint-mode registry too, not the production one
                    service('phd_exceptional_matcher.linter.plan_registry'),
                    false, // $throwOnFailure
                    service(MappingDefectCollector::class),
                ]),
            false, // $throwOnFailure
            service(MappingDefectCollector::class),
        ])
        // the property compiler needs the registry this one serves: being lazy is what lets it be built first
        ->lazy($lazy(ExceptionMappingPlanCompiler::class, true /* $required */))
    ;

    $services->set(MappingDefectCollector::class, MappingDefectCollector::class);
};
