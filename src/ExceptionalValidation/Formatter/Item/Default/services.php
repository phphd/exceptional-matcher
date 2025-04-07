<?php


declare(strict_types=1);

namespace App\DependencyInjection;

use PhPhD\ExceptionalValidation\Formatter\Item\Default\DefaultExceptionViolationFormatter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $services = $containerConfigurator->services();

    $parameters
        ->set('phd_exceptional_validation.translation_domain', param('validator.translation_domain'))
    ;

    $services
        ->set('phd_exceptional_validation.violation_formatter.default', DefaultExceptionViolationFormatter::class)
        ->args([
            service('translator'),
            param('phd_exceptional_validation.translation_domain'),
        ])
        ->tag('exceptional_validation.violation_formatter', ['id' => 'default'])
    ;
};
