<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Validator\Middleware\Messenger;

use PhPhD\ExceptionalValidation\ExceptionMatcher;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Validator\ConstraintViolationListInterface;

return static function (ContainerConfigurator $container, ContainerBuilder $builder): void {
    if (false === $builder->getParameter('phd_exceptional_validation.validator_available')) {
        return;
    }

    if (false === $builder->getParameter('phd_exceptional_validation.messenger_available')) {
        return;
    }

    $container->services()
        ->set('phd_exceptional_validation', ExceptionalValidationMiddleware::class)
        ->public()
        ->args([new Reference(ExceptionMatcher::class.'<'.ConstraintViolationListInterface::class.'>')])
    ;
};
