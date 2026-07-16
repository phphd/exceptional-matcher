<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher;

use Closure;
use PhPhD\ExceptionalMatcher\Exception\MatchedExceptionList;
use PhPhD\ExceptionalMatcher\Rule\Object\ClassMatchingPlanRegistry;
use PhPhD\ExceptionToolkit\Unwrapper\ExceptionUnwrapper;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator, ContainerBuilder $container): void {
    $services = $configurator->services();

    /** @var Closure(class-string):((bool|class-string)) $lazy */
    $lazy = $container->get('phd_exceptional_matcher.lazy_proxy');

    $services
        ->set(ExceptionMatcher::class.'<'.MatchedExceptionList::class.'>', MainExceptionMatcher::class)
        ->public()
        ->args([
            service(ClassMatchingPlanRegistry::class),
            service('phd_exceptional_matcher.exception_unwrapper'),
        ])
        ->lazy($lazy(ExceptionMatcher::class))
    ;

    $services->alias('phd_exceptional_matcher.exception_unwrapper', ExceptionUnwrapper::class);
};
