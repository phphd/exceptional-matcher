<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item;

use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Delegating\DelegatingPropriatedExceptionFormatter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $builder): void {
    if (false === $builder->getParameter('phd_exceptional_validation.validator_enabled')) {
        return;
    }

    $services = $containerConfigurator->services();

    $services
        ->set(PropriatedExceptionFormatter::class, DelegatingPropriatedExceptionFormatter::class)
        ->args([
            tagged_locator(PropriatedExceptionFormatter::class, 'id'),
        ])
    ;

    $builder
        ->registerForAutoconfiguration(PropriatedExceptionFormatter::class)
        ->addTag(PropriatedExceptionFormatter::class)
    ;
};
