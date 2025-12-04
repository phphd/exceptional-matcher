<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition;

use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Class\ExceptionClassMatchConditionFactory;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Closure\ClosureMatchConditionFactory;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Composite\CompositeMatchConditionFactory;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Delegating\DelegatingMatchConditionFactory;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Origin\ExceptionOriginMatchConditionFactory;
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
                inline_service(ClosureMatchConditionFactory::class),
            ],
        ])
    ;
};
