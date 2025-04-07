<?php


declare(strict_types=1);

namespace App\DependencyInjection;

use PhPhD\ExceptionalValidation\Formatter\Item\ViolationList\ViolationListExceptionFormatter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services
        ->set('phd_exceptional_validation.violation_formatter.violation_list_exception', ViolationListExceptionFormatter::class)
        ->tag('exceptional_validation.violation_formatter', ['id' => ViolationListExceptionFormatter::class])
    ;
};
