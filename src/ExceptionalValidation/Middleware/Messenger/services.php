<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Middleware\Messenger;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface as MessengerMiddlewareInterface;

use function interface_exists;

return static function (ContainerConfigurator $container): void {
    if (!interface_exists(MessengerMiddlewareInterface::class)) {
        return;
    }

    $container->services()
        ->set('phd_exceptional_validation', ExceptionalValidationMiddleware::class)
        ->args([new Reference('phd_exceptional_validation.exception_mapper.validator')])
    ;
};
