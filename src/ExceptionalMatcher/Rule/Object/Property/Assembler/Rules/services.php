<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Assembler\Rules;

use Closure;
use PhPhD\ExceptionalMatcher\Rule\Assembler\CompositeRuleSetAssemblerService;
use PhPhD\ExceptionalMatcher\Rule\Assembler\MatchingRuleSetAssemblerService;
use PhPhD\ExceptionalMatcher\Rule\Object\Assembler\ObjectMatchingRuleSetAssembler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Assembler\PropertyMatchingRulesAssembler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Assembler\PropertyMatchingRulesAssemblerService;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $builder): void {
    $services = $containerConfigurator->services();

    /** @var Closure(class-string):((bool|class-string)) $lazy */
    $lazy = $builder->get('phd_exceptional_validation.lazy_proxy');

    $services
        ->set(
            MatchingRuleSetAssemblerService::class.'<'.PropertyMatchingRulesAssembler::class.'>',
            CompositeRuleSetAssemblerService::class,
        )->args([
            [
                service(PropertyMatchingRulesAssemblerService::class),
                service(PropertyNestedValidObjectRuleAssemblerService::class),
                service(PropertyNestedValidIterableRulesAssemblerService::class),
            ],
        ])->lazy($lazy(MatchingRuleSetAssemblerService::class) ?: MatchingRuleSetAssemblerService::class)
    ;

    // Deliberately making these non-lazy
    // as all these are traversed anyway

    $services
        ->set(PropertyNestedValidObjectRuleAssemblerService::class, PropertyNestedValidObjectRuleAssemblerService::class)
        ->args([
            service(MatchingRuleSetAssemblerService::class.'<'.ObjectMatchingRuleSetAssembler::class.'>'),
        ])
    ;

    $services
        ->set(PropertyNestedValidIterableRulesAssemblerService::class, PropertyNestedValidIterableRulesAssemblerService::class)
        ->args([
            service(MatchingRuleSetAssemblerService::class.'<'.ObjectMatchingRuleSetAssembler::class.'>'),
        ])
    ;
};
