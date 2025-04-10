<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\List;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $builder): void {
    if (false === $builder->getParameter('phd_exceptional_validation.validator_enabled')) {
        return;
    }

    $services = $containerConfigurator->services();

    $services
        ->set('phd_exceptional_validation.violations_list_formatter', DefaultExceptionListViolationFormatter::class)
        ->args([
            service('phd_exceptional_validation.violation_formatter'),
        ])
        ->lazy()
        ->tag('proxy', ['interface' => ExceptionListViolationFormatter::class])
    ;
};
