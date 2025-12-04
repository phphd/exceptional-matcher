<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value;

use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchConditionFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services
        ->set(MatchConditionFactory::class.'<'.ValueException::class.'>', ExceptionValueMatchConditionFactory::class)
        ->tag(MatchConditionFactory::class, ['id' => ExceptionValueMatchCondition::class])
    ;
};
