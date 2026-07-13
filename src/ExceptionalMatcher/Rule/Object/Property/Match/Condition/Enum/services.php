<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\MatchConditionCompiler;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(EnumValueMatchConditionCompiler::class, EnumValueMatchConditionCompiler::class)
        ->tag(MatchConditionCompiler::class, ['id' => EnumValueMatchCondition::class])
    ;
};
