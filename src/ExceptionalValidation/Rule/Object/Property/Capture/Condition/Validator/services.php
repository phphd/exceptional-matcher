<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Validator\ValidationFailedExceptionValueMatchCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Validator\ValidationFailedExceptionValueMatchConditionFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services
        ->set('phd_exceptional_validation.match_condition_factory.validation_failed_exception_value', ValidationFailedExceptionValueMatchConditionFactory::class)
        ->tag('exceptional_validation.match_condition_factory', ['id' => ValidationFailedExceptionValueMatchCondition::class])
    ;
};
