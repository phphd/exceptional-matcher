<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\Symfony\Uid;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Blueprint\MatchConditionCompiler;
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
        ->set(MatchConditionCompiler::class.'<'.InvalidUidException::class.'>', UidValueMatchConditionCompiler::class)
        ->tag(MatchConditionCompiler::class, ['id' => UidValueMatchCondition::class])
    ;
};
