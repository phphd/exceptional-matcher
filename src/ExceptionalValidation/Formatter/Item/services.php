<?php

declare(strict_types=1);

use PhPhD\ExceptionalValidation\Formatter\Item\ExceptionViolationFormatter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container, ContainerBuilder $builder): void {
    $builder
        ->registerForAutoconfiguration(ExceptionViolationFormatter::class)
        ->addTag('exceptional_validation.violation_formatter')
    ;
};
