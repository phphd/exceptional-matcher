<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Validator\Formatter\ViolationList;

use PhPhD\ExceptionalMatcher\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Validator\Formatter\ExceptionViolationFormatter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Validator\Exception\ValidationFailedException;

return static function (ContainerConfigurator $configurator, ContainerBuilder $container): void {
    if (false === $container->getParameter('phd_exceptional_matcher.validator_available')) {
        return;
    }

    $services = $configurator->services();

    $services
        ->set(ExceptionViolationFormatter::class.'<'.ViolationsEmbeddedException::class.'>', ViolationsEmbeddedExceptionFormatter::class)
        ->tag(MatchedExceptionFormatter::class, ['id' => ViolationsEmbeddedExceptionFormatter::class])
    ;

    $services->alias(
        ExceptionViolationFormatter::class.'<'.ValidationFailedException::class.'>',
        ExceptionViolationFormatter::class.'<'.ViolationsEmbeddedException::class.'>',
    );
};
