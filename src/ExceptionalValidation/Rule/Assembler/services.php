<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Assembler;

use PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\PropertyRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyNestedValidIterableRulesAssemblerService;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyNestedValidObjectRuleAssemblerService;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Assembler\PropertyCaptureRulesAssemblerService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services
        ->set('phd_exceptional_validation.rule_set_assembler.object', ObjectRuleSetAssemblerService::class)
        ->args([
            service('phd_exceptional_validation.rule_set_assembler.property'),
        ])
    ;

    $services
        ->set('phd_exceptional_validation.rule_set_assembler.property', PropertyRuleSetAssemblerService::class)
        ->args([
            service('phd_exceptional_validation.rule_set_assembler.property.rules'),
        ])
    ;

    $services
        ->set('phd_exceptional_validation.rule_set_assembler.property.rules', CompositeRuleSetAssemblerService::class)
        ->args([
            [
                service('phd_exceptional_validation.rule_set_assembler.property.rules.captures'),
                service('phd_exceptional_validation.rule_set_assembler.property.rules.nested_valid_object'),
                service('phd_exceptional_validation.rule_set_assembler.property.rules.nested_valid_iterable'),
            ],
        ])
    ;

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
        ->lazy()
        ->tag('proxy', ['interface' => CaptureRuleSetAssemblerService::class])
    ;

    $services
        ->set(
            'phd_exceptional_validation.rule_set_assembler.property.rules.nested_valid_iterable',
            PropertyNestedValidIterableRulesAssemblerService::class,
        )
        ->args([
            service('phd_exceptional_validation.rule_set_assembler.object'),
        ])
        ->lazy()
        ->tag('proxy', ['interface' => CaptureRuleSetAssemblerService::class])
    ;
};
