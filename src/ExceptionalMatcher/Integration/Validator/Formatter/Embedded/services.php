<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Embedded;

use PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\ExceptionViolationFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\MatchedExceptionFormatter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Validator\Exception\ValidationFailedException;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator, ContainerBuilder $container): void {
    if (false === $container->getParameter('phd_exceptional_matcher.validator_available')) {
        return;
    }

    $services = $configurator->services();

    $services
        ->set(ExceptionViolationFormatter::class.'<'.ViolationsEmbeddedException::class.'>', ViolationsEmbeddedExceptionFormatter::class)
        ->args([
            service('phd_exceptional_matcher.translator')
                ->ignoreOnInvalid(),
        ])
        ->tag(MatchedExceptionFormatter::class, ['id' => ViolationsEmbeddedExceptionFormatter::class])
    ;

    $services->alias(
        ExceptionViolationFormatter::class.'<'.ValidationFailedException::class.'>',
        ExceptionViolationFormatter::class.'<'.ViolationsEmbeddedException::class.'>',
    );
};
