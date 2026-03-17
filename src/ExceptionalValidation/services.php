<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation;

use Closure;
use PhPhD\ExceptionalValidation\Rule\Assembler\MatchingRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\Exception\MatchedExceptionList;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectMatchingRuleSetAssembler;
use PhPhD\ExceptionToolkit\Unwrapper\ExceptionUnwrapper;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $builder): void {
    $services = $containerConfigurator->services();

    /** @var Closure(class-string):((bool|class-string)) $lazy */
    $lazy = $builder->get('phd_exceptional_validation.lazy_proxy');

    $services
        ->set(ExceptionMatcher::class.'<'.MatchedExceptionList::class.'>', MainExceptionMatcher::class)
        ->public()
        ->args([
            service(MatchingRuleSetAssemblerService::class.'<'.ObjectMatchingRuleSetAssembler::class.'>'),
            service('phd_exceptional_validation.exception_unwrapper'),
        ])
        ->lazy($lazy(ExceptionMatcher::class))
    ;

    $services->alias('phd_exceptional_validation.exception_unwrapper', ExceptionUnwrapper::class);
};
