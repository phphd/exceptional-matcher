<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Validator;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchConditionFactory;
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
