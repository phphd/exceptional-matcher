<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object;

use PhPhD\ExceptionalMatcher\Rule\Object\Autoload\ConstantsAutoloadingCompilerPass;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Throwable;

use function Symfony\Component\DependencyInjection\Loader\Configurator\abstract_arg;
use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(ClassMatchingPlanRegistry::class, ClassMatchingPlanRegistry::class)
        ->args([
            inline_service(ClassMatchingPlanFactory::class)
                ->args([
                    service(MatchConditionCompiler::class.'<'.Throwable::class.'>'),
                ]),
            abstract_arg('Injected by '.ConstantsAutoloadingCompilerPass::class),
        ])
    ;
};
