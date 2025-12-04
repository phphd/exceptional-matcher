<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Validator;

use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchConditionFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $builder): void {
    if (false === $builder->getParameter('phd_exceptional_validation.validator_enabled')) {
        return;
    }

    $services = $containerConfigurator->services();

    $services
        ->set('phd_exceptional_validation.match_condition_factory.validation_failed_exception', ValidationFailedExceptionMatchConditionFactory::class)
        ->tag(MatchConditionFactory::class, ['id' => ValidationFailedExceptionMatchCondition::class])
    ;
};
