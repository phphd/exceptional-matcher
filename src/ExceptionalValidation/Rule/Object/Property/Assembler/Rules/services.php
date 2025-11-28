<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Assembler;

use Closure;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyNestedValidIterableRulesAssemblerService;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyNestedValidObjectRuleAssemblerService;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Assembler\PropertyCaptureRulesAssemblerService;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $builder): void {
    $services = $containerConfigurator->services();

    /** @var Closure(class-string):((bool|class-string)) $lazy */
    $lazy = $builder->get('phd_exceptional_validation.lazy_proxy');

    $services
        ->set('phd_exceptional_validation.rule_set_assembler.property.rules', CompositeRuleSetAssemblerService::class)
        ->args([
            [
                service('phd_exceptional_validation.rule_set_assembler.property.rules.captures'),
                service('phd_exceptional_validation.rule_set_assembler.property.rules.nested_valid_object'),
                service('phd_exceptional_validation.rule_set_assembler.property.rules.nested_valid_iterable'),
            ],
        ])->lazy($lazy(CaptureRuleSetAssemblerService::class) ?: CaptureRuleSetAssemblerService::class)
    ;

    // Deliberately making these non-lazy
    // since all of them are traversed anyway

    $services
        ->set('phd_exceptional_validation.rule_set_assembler.property.rules.captures', PropertyCaptureRulesAssemblerService::class)
        ->args([
            service('phd_exceptional_validation.match_condition_factory'),
        ])
    ;

    $services
        ->set('phd_exceptional_validation.rule_set_assembler.property.rules.nested_valid_object', PropertyNestedValidObjectRuleAssemblerService::class)
        ->args([
            service('phd_exceptional_validation.rule_set_assembler.object'),
        ])
    ;

    $services
        ->set(
            'phd_exceptional_validation.rule_set_assembler.property.rules.nested_valid_iterable',
            PropertyNestedValidIterableRulesAssemblerService::class,
        )
        ->args([
            service('phd_exceptional_validation.rule_set_assembler.object'),
        ])
    ;
};
