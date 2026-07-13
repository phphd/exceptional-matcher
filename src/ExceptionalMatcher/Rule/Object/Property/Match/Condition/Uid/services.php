<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Uid;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;
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
        ->set(MatchConditionCompiler::class.'<'.InvalidUidException::class.'>', InvalidUidExceptionMatchConditionCompiler::class)
        ->tag(MatchConditionCompiler::class, ['id' => InvalidUidExceptionMatchCondition::class])
    ;
};
