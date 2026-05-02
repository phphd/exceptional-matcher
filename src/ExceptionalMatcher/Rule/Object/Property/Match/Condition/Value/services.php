<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Value;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchConditionFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(MatchConditionFactory::class.'<'.ValueException::class.'>', ExceptionValueMatchConditionFactory::class)
        ->tag(MatchConditionFactory::class, ['id' => ExceptionValueMatchCondition::class])
    ;
};
