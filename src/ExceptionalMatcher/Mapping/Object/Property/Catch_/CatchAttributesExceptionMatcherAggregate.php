<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;

use Iterator;
use PhPhD\ExceptionalMatcher\Mapping\Matcher\ExceptionMatcherAggregate;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Blueprint\CatchExceptionMappingBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\PropertyExceptionMappingNode;
use Throwable;

/** @internal */
final class CatchAttributesExceptionMatcherAggregate implements ExceptionMatcherAggregate
{
    public function __construct(
        private readonly PropertyExceptionMappingNode $property,
        /** @var iterable<CatchExceptionMappingBlueprint<Throwable>> */
        private readonly iterable $catchPlans,
    ) {
    }

    public function getExceptionMatchers(): Iterator
    {
        foreach ($this->catchPlans as $catchPlan) {
            yield $catchPlan->bind($this->property);
        }
    }
}
