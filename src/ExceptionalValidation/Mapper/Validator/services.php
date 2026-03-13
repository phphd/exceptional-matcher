<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator;

use Closure;
use PhPhD\ExceptionalValidation\Mapper\ExceptionMapper;
use PhPhD\ExceptionalValidation\Rule\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\MatchedExceptionList;
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
        ->set(ExceptionMapper::class.'<'.ConstraintViolationListInterface::class.'>', ExceptionToViolationListMapper::class)
        ->public()
        ->args([
            service(ExceptionMapper::class.'<'.MatchedExceptionList::class.'>'),
            service(MatchedExceptionFormatter::class.'<'.Throwable::class.','.ConstraintViolationInterface::class.'>'),
        ])
        ->lazy($lazy(ExceptionMapper::class))
    ;
};
