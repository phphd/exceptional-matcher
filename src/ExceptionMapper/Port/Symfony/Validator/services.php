<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Port\Symfony\Validator;

use Closure;
use PhPhD\ExceptionMapper\ExceptionMapper;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\ExceptionMatchList;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\MatchedExceptionFormatter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator, ContainerBuilder $container): void {
    if (false === $container->getParameter('phd_exception_mapper.validator_available')) {
        return;
    }

    /** @var Closure(class-string):((bool|class-string)) $hintLazy */
    $hintLazy = $container->get('phd_exception_mapper.hint_lazy_proxy');

    $services = $configurator->services();

    $services
        ->set(ExceptionMapper::class.'<'.ConstraintViolationListInterface::class.'>', ExceptionToViolationListMapper::class)
        ->public()
        ->args([
            service(ExceptionMapper::class.'<'.ExceptionMatchList::class.'>'),
            service(MatchedExceptionFormatter::class.'<'.Throwable::class.','.ConstraintViolationInterface::class.'>'),
        ])
        ->lazy($hintLazy(ExceptionMapper::class))
    ;
};
