<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition;

use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Class\ExceptionClassMatchConditionFactory;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Closure\SimpleIfClosureMatchConditionFactory;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\CompositeMatchConditionFactory;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Delegating\DelegatingMatchConditionFactory;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Origin\ExceptionOriginMatchConditionFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Throwable;

use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services
        ->set(MatchConditionFactory::class.'<'.Throwable::class.'>', CompositeMatchConditionFactory::class)
        ->args([
            [
                inline_service(ExceptionClassMatchConditionFactory::class),
                inline_service(ExceptionOriginMatchConditionFactory::class),
                inline_service(DelegatingMatchConditionFactory::class)
                    ->args([
                        tagged_locator(MatchConditionFactory::class, 'id'),
                    ]),
                inline_service(SimpleIfClosureMatchConditionFactory::class),
            ],
        ])
    ;
};
