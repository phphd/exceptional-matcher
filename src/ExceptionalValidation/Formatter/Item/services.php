<?php


declare(strict_types=1);

namespace App\DependencyInjection;

use PhPhD\ExceptionalValidation\Formatter\Item\DefaultExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Formatter\Item\Delegating\DelegatingExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Formatter\Item\ExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Formatter\Item\Validator\ViolationListExceptionFormatter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $builder): void {
    $parameters = $containerConfigurator->parameters();
    $services = $containerConfigurator->services();

    $parameters
        ->set('phd_exceptional_validation.translation_domain', param('validator.translation_domain'))
    ;

    $services
        ->set('phd_exceptional_validation.violation_formatter', DelegatingExceptionViolationFormatter::class)
        ->args([
            tagged_locator('exceptional_validation.violation_formatter', 'id'),
        ])
    ;

    $services
        ->set('phd_exceptional_validation.violation_formatter.default', DefaultExceptionViolationFormatter::class)
        ->args([
            service('translator'),
            param('phd_exceptional_validation.translation_domain'),
        ])
        ->tag('exceptional_validation.violation_formatter', ['id' => 'default'])
    ;

    $services
        ->set('phd_exceptional_validation.violation_formatter.violation_list_exception', ViolationListExceptionFormatter::class)
        ->tag('exceptional_validation.violation_formatter', ['id' => ViolationListExceptionFormatter::class])
    ;

    $builder
        ->registerForAutoconfiguration(ExceptionViolationFormatter::class)
        ->addTag('exceptional_validation.violation_formatter')
    ;
};
