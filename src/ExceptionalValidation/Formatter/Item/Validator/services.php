<?php


declare(strict_types=1);

namespace App\DependencyInjection;

use PhPhD\ExceptionalValidation\Formatter\Item\Validator\ValidationFailedExceptionFormatter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services
        ->set('phd_exceptional_validation.violation_formatter.validation_failed_exception', ValidationFailedExceptionFormatter::class)
        ->args([
            service('phd_exceptional_validation.violation_formatter.violation_list_exception'),
        ])
        ->tag('exceptional_validation.violation_formatter', ['id' => ValidationFailedExceptionFormatter::class])
    ;
};
