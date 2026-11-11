<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\Value;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Blueprint\MatchConditionCompiler;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(MatchConditionCompiler::class.'<'.ValueException::class.'>', ExceptionValueMatchConditionCompiler::class)
        ->tag(MatchConditionCompiler::class, ['id' => ExceptionValueMatchCondition::class])
    ;
};
