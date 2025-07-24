<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Middleware\Messenger;

use PhPhD\ExceptionalValidation\Mapper\ExceptionMapper;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Validator\ConstraintViolationListInterface;

return static function (ContainerConfigurator $container, ContainerBuilder $builder): void {
    if (false === $builder->getParameter('phd_exceptional_validation.validator_enabled')) {
        return;
    }

    if (false === $builder->getParameter('phd_exceptional_validation.messenger_enabled')) {
        return;
    }

    $container->services()
        ->set('phd_exceptional_validation', ExceptionalValidationMiddleware::class)
        ->args([new Reference(ExceptionMapper::class.'<'.ConstraintViolationListInterface::class.'>')])
    ;
};
