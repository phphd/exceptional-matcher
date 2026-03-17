<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Validator\Formatter\Validator;

use PhPhD\ExceptionalMatcher\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Validator\Formatter\ExceptionViolationFormatter;
use PhPhD\ExceptionalMatcher\Validator\Formatter\ViolationList\ViolationListException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Validator\Exception\ValidationFailedException;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $builder): void {
    if (false === $builder->getParameter('phd_exceptional_matcher.validator_available')) {
        return;
    }

    $services = $containerConfigurator->services();

    $services
        ->set(ExceptionViolationFormatter::class.'<'.ValidationFailedException::class.'>', ValidationFailedExceptionFormatter::class)
        ->args([
            service(ExceptionViolationFormatter::class.'<'.ViolationListException::class.'>'),
        ])
        ->tag(MatchedExceptionFormatter::class, ['id' => ValidationFailedExceptionFormatter::class])
    ;
};
