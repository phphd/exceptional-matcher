<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Default;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $builder): void {
    if (false === $builder->getParameter('phd_exceptional_validation.validator_enabled')) {
        return;
    }

    $parameters = $containerConfigurator->parameters();
    $services = $containerConfigurator->services();

    $parameters
        ->set('phd_exceptional_validation.translation_domain', param('validator.translation_domain'))
    ;

    $services
        ->set('phd_exceptional_validation.violation_formatter.default', DefaultExceptionViolationFormatter::class)
        ->args([
            service('translator'),
            param('phd_exceptional_validation.translation_domain'),
        ])
        ->tag('exceptional_validation.violation_formatter', ['id' => 'default'])
    ;
};
