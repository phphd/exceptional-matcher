<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Assembler;

use PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectRuleSetAssemblerService;
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
};
