<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter;

use PhPhD\ExceptionalValidation\Rule\Exception\Formatter\Delegating\DelegatingPropriatedExceptionFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\Formatter\PropriatedExceptionFormatter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Throwable;

use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $builder): void {
    if (false === $builder->getParameter('phd_exceptional_validation.validator_enabled')) {
        return;
    }

    $services = $containerConfigurator->services();

    $services
        ->set(
            PropriatedExceptionFormatter::class.'<'.Throwable::class.','.ConstraintViolationInterface::class.'>',
            DelegatingPropriatedExceptionFormatter::class,
        )->args([
            tagged_locator(PropriatedExceptionFormatter::class, 'id'),
        ])
    ;
};
