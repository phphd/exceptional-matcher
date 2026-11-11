<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified\Symfony\Validator\Embedded;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified\Symfony\Validator\ExceptionViolationFormatter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Validator\Exception\ValidationFailedException;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator, ContainerBuilder $container): void {
    if (false === $container->getParameter('phd_exception_mapper.validator_available')) {
        return;
    }

    $services = $configurator->services();

    $services
        ->set(ExceptionViolationFormatter::class.'<'.ViolationsEmbeddedException::class.'>', ViolationsEmbeddedExceptionFormatter::class)
        ->args([
            service('phd_exception_mapper.translator')
                ->nullOnInvalid(),
        ])
        ->tag(MatchedExceptionFormatter::class, ['id' => ViolationsEmbeddedExceptionFormatter::class])
    ;

    $services->alias(
        ExceptionViolationFormatter::class.'<'.ValidationFailedException::class.'>',
        ExceptionViolationFormatter::class.'<'.ViolationsEmbeddedException::class.'>',
    );
};
