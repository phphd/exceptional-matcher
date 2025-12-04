<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper;

use Closure;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\Exception\PropriatedException;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectRuleSetAssembler;
use PhPhD\ExceptionToolkit\Unwrapper\ExceptionUnwrapper;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $builder): void {
    $services = $containerConfigurator->services();

    /** @var Closure(class-string):((bool|class-string)) $lazy */
    $lazy = $builder->get('phd_exceptional_validation.lazy_proxy');

    $services
        ->set(ExceptionMapper::class.'<non-empty-list<'.PropriatedException::class.'<Throwable>>>', DefaultExceptionMapper::class)
        ->public()
        ->args([
            service(CaptureRuleSetAssemblerService::class.'<'.ObjectRuleSetAssembler::class.'>'),
            service('phd_exceptional_validation.exception_unwrapper'),
        ])
        ->lazy($lazy(ExceptionMapper::class))
    ;

    $services->alias('phd_exceptional_validation.exception_unwrapper', ExceptionUnwrapper::class);
};
