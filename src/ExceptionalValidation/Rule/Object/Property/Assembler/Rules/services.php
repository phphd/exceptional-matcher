<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules;

use Closure;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\Assembler\CompositeRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Assembler\PropertyCaptureRulesAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Assembler\PropertyCaptureRulesAssemblerService;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $builder): void {
    $services = $containerConfigurator->services();

    /** @var Closure(class-string):((bool|class-string)) $lazy */
    $lazy = $builder->get('phd_exceptional_validation.lazy_proxy');

    $services
        ->set(
            CaptureRuleSetAssemblerService::class.'<'.PropertyCaptureRulesAssembler::class.'>',
            CompositeRuleSetAssemblerService::class,
        )->args([
            [
                service(PropertyCaptureRulesAssemblerService::class),
                service(PropertyNestedValidObjectRuleAssemblerService::class),
                service(PropertyNestedValidIterableRulesAssemblerService::class),
            ],
        ])->lazy($lazy(CaptureRuleSetAssemblerService::class) ?: CaptureRuleSetAssemblerService::class)
    ;

    // Deliberately making these non-lazy
    // as all these are traversed anyway

    $services
        ->set(PropertyNestedValidObjectRuleAssemblerService::class, PropertyNestedValidObjectRuleAssemblerService::class)
        ->args([
            service(CaptureRuleSetAssemblerService::class.'<'.ObjectRuleSetAssembler::class.'>'),
        ])
    ;

    $services
        ->set(PropertyNestedValidIterableRulesAssemblerService::class, PropertyNestedValidIterableRulesAssemblerService::class)
        ->args([
            service(CaptureRuleSetAssemblerService::class.'<'.ObjectRuleSetAssembler::class.'>'),
        ])
    ;
};
