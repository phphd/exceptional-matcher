<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Blueprint;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Class\ExceptionClassMatchConditionCompiler;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Closure\SimpleIfClosureMatchConditionCompiler;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Composite\CompositeMatchConditionCompiler;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\QualifiedMatchConditionCompiler;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\TraceOrigin\ExceptionTraceOriginMatchConditionCompiler;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Throwable;

use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(MatchConditionCompiler::class.'<'.Throwable::class.'>', CompositeMatchConditionCompiler::class)
        ->arg('$compilers', [
            inline_service(ExceptionClassMatchConditionCompiler::class),
            inline_service(ExceptionTraceOriginMatchConditionCompiler::class),
            inline_service(QualifiedMatchConditionCompiler::class)
                ->args([
                    tagged_locator(MatchConditionCompiler::class, 'id'),
                ]),
            inline_service(SimpleIfClosureMatchConditionCompiler::class),
        ])
    ;
};
