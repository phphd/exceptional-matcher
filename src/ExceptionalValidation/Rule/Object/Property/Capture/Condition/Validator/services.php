<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Validator;

use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchConditionFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Validator\Exception\ValidationFailedException;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $builder): void {
    if (false === $builder->getParameter('phd_exceptional_validation.validator_available')) {
        return;
    }

    $services = $containerConfigurator->services();

    $services
        ->set(MatchConditionFactory::class.'<'.ValidationFailedException::class.'>', ValidationFailedExceptionMatchConditionFactory::class)
        ->tag(MatchConditionFactory::class, ['id' => ValidationFailedExceptionMatchCondition::class])
    ;
};
