<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\ViolationList;

use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\PropriatedExceptionFormatter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $builder): void {
    if (false === $builder->getParameter('phd_exceptional_validation.validator_enabled')) {
        return;
    }

    $services = $containerConfigurator->services();

    $services
        ->set(PropriatedExceptionFormatter::class.'<'.ViolationListException::class.'>', ViolationListExceptionFormatter::class)
        ->tag(PropriatedExceptionFormatter::class, ['id' => ViolationListExceptionFormatter::class])
    ;
};
