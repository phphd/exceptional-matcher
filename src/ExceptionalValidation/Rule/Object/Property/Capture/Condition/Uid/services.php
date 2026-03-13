<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Uid;

use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchConditionFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;

use function class_exists;
use function property_exists;

return static function (ContainerConfigurator $containerConfigurator): void {
    if (!class_exists(InvalidUidException::class) || !property_exists(InvalidUidException::class, 'invalidValue')) {
        return;
    }

    $services = $containerConfigurator->services();

    $services
        ->set(MatchConditionFactory::class.'<'.InvalidUidException::class.'>', InvalidUidExceptionMatchConditionFactory::class)
        ->tag(MatchConditionFactory::class, ['id' => InvalidUidExceptionMatchCondition::class])
    ;
};
