<?php

declare(strict_types=1);

use PhPhD\ExceptionalValidation\Handler\DefaultExceptionHandler;
use PhPhD\ExceptionalValidation\Handler\ExceptionHandler;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services
        ->set('phd_exceptional_validation.exception_handler', DefaultExceptionHandler::class)
        ->args([
            service('phd_exceptional_validation.rule_set_assembler'),
            service('phd_exceptional_validation.exception_unwrapper'),
            service('phd_exceptional_validation.violations_list_formatter'),
        ])
        ->lazy()
        ->tag('proxy', ['interface' => ExceptionHandler::class])
    ;

    $services->alias('phd_exceptional_validation.exception_unwrapper', 'phd_exception_toolkit.exception_unwrapper');

    $services->alias('phd_exceptional_validation.rule_set_assembler', 'phd_exceptional_validation.rule_set_assembler.object');
};
