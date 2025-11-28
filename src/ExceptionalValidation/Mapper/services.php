<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper;

use Closure;
use PhPhD\ExceptionalValidation\Rule\Exception\CapturedException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $builder): void {
    $services = $containerConfigurator->services();

    /** @var Closure(class-string):((bool|class-string)) $lazy */
    $lazy = $builder->get('phd_exceptional_validation.lazy_proxy');

    $services
        ->set(ExceptionMapper::class.'<non-empty-list<'.CapturedException::class.'<Throwable>>>', DefaultExceptionMapper::class)
        ->public()
        ->args([
            service('phd_exceptional_validation.rule_set_assembler'),
            service('phd_exceptional_validation.exception_unwrapper'),
        ])
        ->lazy($lazy(ExceptionMapper::class))
    ;

    $services->alias('phd_exceptional_validation.exception_unwrapper', 'phd_exception_toolkit.exception_unwrapper');

    $services->alias('phd_exceptional_validation.rule_set_assembler', 'phd_exceptional_validation.rule_set_assembler.object');
};
