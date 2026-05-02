<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Exception\Formatter;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator, ContainerBuilder $container): void {
    $container
        ->registerForAutoconfiguration(MatchedExceptionFormatter::class)
        ->addTag(MatchedExceptionFormatter::class)
    ;
};
