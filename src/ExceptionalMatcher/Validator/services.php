<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Validator;

use Closure;
use PhPhD\ExceptionalMatcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Rule\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Rule\Exception\MatchedExceptionList;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $builder): void {
    if (false === $builder->getParameter('phd_exceptional_validation.validator_available')) {
        return;
    }

    /** @var Closure(class-string):((bool|class-string)) $lazy */
    $lazy = $builder->get('phd_exceptional_validation.lazy_proxy');

    $services = $containerConfigurator->services();

    $services
        ->set(ExceptionMatcher::class.'<'.ConstraintViolationListInterface::class.'>', ExceptionToViolationListMatcher::class)
        ->public()
        ->args([
            service(ExceptionMatcher::class.'<'.MatchedExceptionList::class.'>'),
            service(MatchedExceptionFormatter::class.'<'.Throwable::class.','.ConstraintViolationInterface::class.'>'),
        ])
        ->lazy($lazy(ExceptionMatcher::class))
    ;
};
