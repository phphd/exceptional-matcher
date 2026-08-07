<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Validator;

use Closure;
use PhPhD\ExceptionalMatcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\entos\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\MatchedExceptionList;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator, ContainerBuilder $container): void {
    if (false === $container->getParameter('phd_exceptional_matcher.validator_available')) {
        return;
    }

    /** @var Closure(class-string):((bool|class-string)) $hintLazy */
    $hintLazy = $container->get('phd_exceptional_matcher.hint_lazy');

    $services = $configurator->services();

    $services
        ->set(ExceptionMatcher::class.'<'.ConstraintViolationListInterface::class.'>', ExceptionToViolationListMatcher::class)
        ->public()
        ->args([
            service(ExceptionMatcher::class.'<'.MatchedExceptionList::class.'>'),
            service(MatchedExceptionFormatter::class.'<'.Throwable::class.','.ConstraintViolationInterface::class.'>'),
        ])
        ->lazy($hintLazy(ExceptionMatcher::class))
    ;
};
