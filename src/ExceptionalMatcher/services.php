<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher;

use Closure;
use PhPhD\ExceptionalMatcher\Mapping\Object\entos\Plan\Registry\ObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\MatchedExceptionList;
use PhPhD\ExceptionToolkit\Unwrapper\ExceptionUnwrapper;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator, ContainerBuilder $container): void {
    $services = $configurator->services();

    /** @var Closure(class-string):((bool|class-string)) $hintLazy */
    $hintLazy = $container->get('phd_exceptional_matcher.hint_lazy');

    $services
        ->set(ExceptionMatcher::class.'<'.MatchedExceptionList::class.'>', MainExceptionMatcher::class)
        ->public()
        ->args([
            service(ObjectExceptionMappingPlanRegistry::class),
            service('phd_exceptional_matcher.exception_unwrapper'),
        ])
        ->lazy($hintLazy(ExceptionMatcher::class))
    ;

    $services->alias('phd_exceptional_matcher.exception_unwrapper', ExceptionUnwrapper::class);
};
