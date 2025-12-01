<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Assembler;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services
        ->set(PropertyCaptureRulesAssemblerService::class, PropertyCaptureRulesAssemblerService::class)
        ->args([
            service('phd_exceptional_validation.match_condition_factory'),
        ])
    ;
};
