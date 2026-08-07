<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\Integration\Validator;

use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\_Compiler\MatchConditionCompiler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Validator\Exception\ValidationFailedException;

return static function (ContainerConfigurator $configurator, ContainerBuilder $container): void {
    if (false === $container->getParameter('phd_exceptional_matcher.validator_available')) {
        return;
    }

    $services = $configurator->services();

    $services
        ->set(MatchConditionCompiler::class.'<'.ValidationFailedException::class.'>', ValidationFailedExceptionMatchConditionCompiler::class)
        ->tag(MatchConditionCompiler::class, ['id' => ValidationFailedExceptionMatchCondition::class])
    ;
};
