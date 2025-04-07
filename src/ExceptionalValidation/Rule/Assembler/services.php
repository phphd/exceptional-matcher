<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Assembler\CompositeRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\IterableOfObjectsRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\Rules\ObjectRulesAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\PropertyRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyNestedValidIterableRulesAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyNestedValidObjectRuleAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Assembler\PropertyCaptureRulesAssembler;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services
        ->set('phd_exceptional_validation.rule_set_assembler.object', ObjectRuleSetAssembler::class)
        ->args([
            service('phd_exceptional_validation.rule_set_assembler.object.rules'),
        ])
    ;

    $services
        ->set('phd_exceptional_validation.rule_set_assembler.object.rules', ObjectRulesAssembler::class)
        ->args([
            service('phd_exceptional_validation.rule_set_assembler.property'),
        ])
    ;

    $services
        ->set('phd_exceptional_validation.rule_set_assembler.property', PropertyRuleSetAssembler::class)
        ->args([
            service('phd_exceptional_validation.rule_set_assembler.property.rules'),
        ])
    ;

    $services
        ->set('phd_exceptional_validation.rule_set_assembler.property.rules', CompositeRuleSetAssembler::class)
        ->args([
            [
                service('phd_exceptional_validation.rule_set_assembler.property.rules.captures'),
                service('phd_exceptional_validation.rule_set_assembler.property.rules.nested_valid_object'),
                service('phd_exceptional_validation.rule_set_assembler.property.rules.nested_valid_iterable'),
            ],
        ])
    ;

    $services
        ->set('phd_exceptional_validation.rule_set_assembler.property.rules.captures', PropertyCaptureRulesAssembler::class)
        ->args([
            service('phd_exceptional_validation.match_condition_factory'),
        ])
    ;

    $services
        ->set('phd_exceptional_validation.rule_set_assembler.property.rules.nested_valid_object', PropertyNestedValidObjectRuleAssembler::class)
        ->args([
            service('phd_exceptional_validation.rule_set_assembler.object'),
        ])
        ->lazy()
        ->tag('proxy', ['interface' => CaptureRuleSetAssembler::class])
    ;

    $services
        ->set(
            'phd_exceptional_validation.rule_set_assembler.property.rules.nested_valid_iterable',
            PropertyNestedValidIterableRulesAssembler::class,
        )
        ->args([
            service('phd_exceptional_validation.rule_set_assembler.iterable_of_objects'),
        ])
        ->lazy()
        ->tag('proxy', ['interface' => CaptureRuleSetAssembler::class])
    ;

    $services
        ->set('phd_exceptional_validation.rule_set_assembler.iterable_of_objects', IterableOfObjectsRuleSetAssembler::class)
        ->args([
            service('phd_exceptional_validation.rule_set_assembler.object'),
        ])
    ;
};
