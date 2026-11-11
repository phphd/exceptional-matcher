<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified\Symfony\Validator;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\ExceptionMatchFormatter;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified\QualifiedExceptionMatchFormatter;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified\Symfony\Validator\Main\MainExceptionViolationFormatter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Throwable;

use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $configurator, ContainerBuilder $container): void {
    if (false === $container->getParameter('phd_exception_mapper.validator_available')) {
        return;
    }

    $services = $configurator->services();

    $services
        ->set(
            ExceptionMatchFormatter::class.'<'.Throwable::class.','.ConstraintViolationInterface::class.'>',
            QualifiedExceptionMatchFormatter::class,
        )->args([
            tagged_locator(ExceptionMatchFormatter::class, 'id'),
            MainExceptionViolationFormatter::class,
        ])
    ;
};
