<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Uid;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchConditionFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;

use function class_exists;
use function property_exists;

return static function (ContainerConfigurator $configurator): void {
    if (!class_exists(InvalidUidException::class) || !property_exists(InvalidUidException::class, 'invalidValue')) {
        return;
    }

    $services = $configurator->services();

    $services
        ->set(MatchConditionFactory::class.'<'.InvalidUidException::class.'>', InvalidUidExceptionMatchConditionFactory::class)
        ->tag(MatchConditionFactory::class, ['id' => InvalidUidExceptionMatchCondition::class])
    ;
};
