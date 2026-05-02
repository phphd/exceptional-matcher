<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Validator\Formatter;

use PhPhD\ExceptionalMatcher\Exception\Formatter\Delegating\DelegatingMatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Exception\Formatter\MatchedExceptionFormatter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Throwable;

use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $configurator, ContainerBuilder $container): void {
    if (false === $container->getParameter('phd_exceptional_matcher.validator_available')) {
        return;
    }

    $services = $configurator->services();

    $services
        ->set(
            MatchedExceptionFormatter::class.'<'.Throwable::class.','.ConstraintViolationInterface::class.'>',
            DelegatingMatchedExceptionFormatter::class,
        )->args([
            tagged_locator(MatchedExceptionFormatter::class, 'id'),
        ])
    ;
};
