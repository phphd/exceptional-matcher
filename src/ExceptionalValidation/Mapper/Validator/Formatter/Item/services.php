<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item;

use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Delegating\DelegatingExceptionViolationFormatter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $builder): void {
    if (false === $builder->getParameter('phd_exceptional_validation.validator_enabled')) {
        return;
    }

    $services = $containerConfigurator->services();

    $services
        ->set('phd_exceptional_validation.violation_formatter', DelegatingExceptionViolationFormatter::class)
        ->args([
            tagged_locator('exceptional_validation.violation_formatter', 'id'),
        ])
    ;

    $builder
        ->registerForAutoconfiguration(ExceptionViolationFormatter::class)
        ->addTag('exceptional_validation.violation_formatter')
    ;
};
