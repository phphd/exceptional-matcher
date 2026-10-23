<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Main;

use PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\ExceptionViolationFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\MatchedExceptionFormatter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator, ContainerBuilder $container): void {
    if (false === $container->getParameter('phd_exceptional_matcher.validator_available')) {
        return;
    }

    $services = $configurator->services();

    $services
        ->set(ExceptionViolationFormatter::class.'<Throwable>', MainExceptionViolationFormatter::class)
        ->args([
            service('phd_exceptional_matcher.translator')
                ->nullOnInvalid(),
        ])
        ->tag(MatchedExceptionFormatter::class, ['id' => MainExceptionViolationFormatter::class])
    ;
};
