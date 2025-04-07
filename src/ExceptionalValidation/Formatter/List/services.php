<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use PhPhD\ExceptionalValidation\Formatter\List\DefaultExceptionListViolationFormatter;
use PhPhD\ExceptionalValidation\Formatter\List\ExceptionListViolationFormatter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
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
