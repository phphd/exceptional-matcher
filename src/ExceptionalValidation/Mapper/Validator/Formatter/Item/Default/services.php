<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Default;

use Closure;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\ExceptionViolationFormatter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $builder): void {
    if (false === $builder->getParameter('phd_exceptional_validation.validator_enabled')) {
        return;
    }

    $parameters = $containerConfigurator->parameters();
    $services = $containerConfigurator->services();

    $services
        ->set(ExceptionViolationFormatter::class.'<Throwable>', DefaultExceptionViolationFormatter::class)
        ->args([
            new Reference('phd_exceptional_validation.translator', ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
        ])
        ->tag(ExceptionViolationFormatter::class, ['id' => 'default'])
    ;

    $services
        ->set('phd_exceptional_validation.translator', Closure::class) // is removed if @translator is not found
        ->factory([DefaultExceptionViolationFormatter::class, 'translator'])
        ->args([
            service('translator'),
            param('phd_exceptional_validation.translation_domain'),
        ])
    ;

    $parameters // is removed if @translator is not found
        ->set('phd_exceptional_validation.translation_domain', param('validator.translation_domain'))
    ;
};
