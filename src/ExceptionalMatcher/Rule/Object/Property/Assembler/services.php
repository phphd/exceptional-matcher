<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Assembler;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Assembler\PropertyMatchingRuleSetAssembler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Assembler\PropertyMatchingRuleSetAssemblerService;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Assembler\PropertyMatchingRulesAssembler;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services
        ->set(MatchingRuleSetAssemblerService::class.'<'.PropertyMatchingRuleSetAssembler::class.'>', PropertyMatchingRuleSetAssemblerService::class)
        ->args([
            service(MatchingRuleSetAssemblerService::class.'<'.PropertyMatchingRulesAssembler::class.'>'),
        ])
    ;
};
