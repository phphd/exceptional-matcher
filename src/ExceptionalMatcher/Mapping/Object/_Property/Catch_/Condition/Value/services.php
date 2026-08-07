<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\Value;

use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Catch_\Condition\_Compiler\MatchConditionCompiler;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(MatchConditionCompiler::class.'<'.ValueException::class.'>', ExceptionValueMatchConditionCompiler::class)
        ->tag(MatchConditionCompiler::class, ['id' => ExceptionValueMatchCondition::class])
    ;
};
