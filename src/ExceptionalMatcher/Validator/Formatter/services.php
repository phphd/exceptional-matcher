<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Validator\Formatter;

use PhPhD\ExceptionalMatcher\Rule\Exception\Formatter\Delegating\DelegatingMatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Rule\Exception\Formatter\MatchedExceptionFormatter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Throwable;

use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $builder): void {
    if (false === $builder->getParameter('phd_exceptional_matcher.validator_available')) {
        return;
    }

    $services = $containerConfigurator->services();

    $services
        ->set(
            MatchedExceptionFormatter::class.'<'.Throwable::class.','.ConstraintViolationInterface::class.'>',
            DelegatingMatchedExceptionFormatter::class,
        )->args([
            tagged_locator(MatchedExceptionFormatter::class, 'id'),
        ])
    ;
};
