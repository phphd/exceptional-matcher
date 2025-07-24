<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator;

use PhPhD\ExceptionalValidation\Mapper\ExceptionMapper;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\List\ExceptionListViolationFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\CapturedException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Validator\ConstraintViolationListInterface;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $builder): void {
    if (false === $builder->getParameter('phd_exceptional_validation.validator_enabled')) {
        return;
    }

    $services = $containerConfigurator->services();

    $services
        ->set(ExceptionMapper::class.'<'.ConstraintViolationListInterface::class.'>', ExceptionViolationListMapper::class)
        ->args([
            service(ExceptionMapper::class.'<non-empty-list<'.CapturedException::class.'<Throwable>>>'),
            service(ExceptionListViolationFormatter::class),
        ])
        ->lazy()
        ->tag('proxy', ['interface' => ExceptionMapper::class])
    ;
};
