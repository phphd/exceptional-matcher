<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\entos\Compiler;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Class\ExceptionClassMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Closure\SimpleIfClosureMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite\CompositeMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Delegating\DelegatingMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Origin\ExceptionOriginMatchConditionCompiler;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Throwable;

use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(MatchConditionCompiler::class.'<'.Throwable::class.'>', CompositeMatchConditionCompiler::class)
        ->args([
            [
                inline_service(ExceptionClassMatchConditionCompiler::class),
                inline_service(ExceptionOriginMatchConditionCompiler::class),
                inline_service(DelegatingMatchConditionCompiler::class)
                    ->args([
                        tagged_locator(MatchConditionCompiler::class, 'id'),
                    ]),
                inline_service(SimpleIfClosureMatchConditionCompiler::class),
            ],
        ])
    ;
};
