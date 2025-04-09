<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator;

use PhPhD\ExceptionalValidation\Mapper\ExceptionMapper;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services
        ->set('phd_exceptional_validation.exception_mapper.validator', ExceptionViolationListMapper::class)
        ->args([
            service('phd_exceptional_validation.exception_mapper'),
            service('phd_exceptional_validation.violations_list_formatter'),
        ])
        ->lazy()
        ->tag('proxy', ['interface' => ExceptionMapper::class])
    ;
};
