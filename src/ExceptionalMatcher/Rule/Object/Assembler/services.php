<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Assembler;

use PhPhD\ExceptionalMatcher\Rule\Assembler\MatchingRuleSetAssemblerService;
use PhPhD\ExceptionalMatcher\Rule\Object\Assembler\Autoload\ConstantsAutoloadingCompilerPass;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Assembler\PropertyMatchingRuleSetAssembler;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\abstract_arg;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(MatchingRuleSetAssemblerService::class.'<'.ObjectMatchingRuleSetAssembler::class.'>', ObjectMatchingRuleSetAssemblerService::class)
        ->args([
            service(MatchingRuleSetAssemblerService::class.'<'.PropertyMatchingRuleSetAssembler::class.'>'),
            abstract_arg('Injected by '.ConstantsAutoloadingCompilerPass::class),
        ])
    ;
};
