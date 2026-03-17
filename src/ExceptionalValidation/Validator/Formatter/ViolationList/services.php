<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Validator\Formatter\ViolationList;

use PhPhD\ExceptionalValidation\Rule\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalValidation\Validator\Formatter\ExceptionViolationFormatter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $builder): void {
    if (false === $builder->getParameter('phd_exceptional_validation.validator_available')) {
        return;
    }

    $services = $containerConfigurator->services();

    $services
        ->set(ExceptionViolationFormatter::class.'<'.ViolationListException::class.'>', ViolationListExceptionFormatter::class)
        ->tag(MatchedExceptionFormatter::class, ['id' => ViolationListExceptionFormatter::class])
    ;
};
