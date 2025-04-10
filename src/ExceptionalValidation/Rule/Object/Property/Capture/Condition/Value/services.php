<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services
        ->set('phd_exceptional_validation.match_condition_factory.value', ExceptionValueMatchConditionFactory::class)
        ->tag('exceptional_validation.match_condition_factory', ['id' => ExceptionValueMatchCondition::class])
    ;
};
