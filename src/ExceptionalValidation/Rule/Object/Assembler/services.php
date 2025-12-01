<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Assembler;

use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\PropertyRuleSetAssembler;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services
        ->set(CaptureRuleSetAssemblerService::class.'<'.ObjectRuleSetAssembler::class.'>', ObjectRuleSetAssemblerService::class)
        ->args([
            service(CaptureRuleSetAssemblerService::class.'<'.PropertyRuleSetAssembler::class.'>'),
        ])
    ;
};
