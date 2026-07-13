<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Assembler;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Throwable;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(PropertyMatchingRulesAssemblerService::class, PropertyMatchingRulesAssemblerService::class)
        ->args([
            service(MatchConditionCompiler::class.'<'.Throwable::class.'>'),
        ])
    ;
};
