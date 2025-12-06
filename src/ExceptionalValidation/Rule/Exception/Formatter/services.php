<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Exception\Formatter;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $builder): void {
    $builder
        ->registerForAutoconfiguration(MatchedExceptionFormatter::class)
        ->addTag(MatchedExceptionFormatter::class)
    ;
};
