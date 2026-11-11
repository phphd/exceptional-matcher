<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Port\Symfony\Validator\Middleware\Messenger;

use PhPhD\ExceptionMapper\ExceptionMapper;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Validator\ConstraintViolationListInterface;

return static function (ContainerConfigurator $container, ContainerBuilder $builder): void {
    if (false === $builder->getParameter('phd_exception_mapper.validator_available')) {
        return;
    }

    if (false === $builder->getParameter('phd_exception_mapper.messenger_available')) {
        return;
    }

    $container->services()
        ->set('phd_exceptional_validation', ExceptionalValidationMiddleware::class)
        ->args([new Reference(ExceptionMapper::class.'<'.ConstraintViolationListInterface::class.'>')])
    ;
};
