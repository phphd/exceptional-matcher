<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object;

use PhPhD\ExceptionalMatcher\Mapping\Autoload\ConstantsAutoloadingCompilerPass;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Compiler\ObjectExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\_Compiler\PropertyExceptionMappingPlanCompiler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Throwable;

use function Symfony\Component\DependencyInjection\Loader\Configurator\abstract_arg;
use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(ObjectExceptionMappingPlanRegistry::class, ObjectExceptionMappingPlanRegistry::class)
        ->args([
            inline_service(ObjectExceptionMappingPlanCompiler::class)
                ->args([
                    inline_service(PropertyExceptionMappingPlanCompiler::class)
                        ->args([
                            service(MatchConditionCompiler::class.'<'.Throwable::class.'>'),
                        ]),
                ]),
            abstract_arg('Injected by '.ConstantsAutoloadingCompilerPass::class),
        ]);
};
