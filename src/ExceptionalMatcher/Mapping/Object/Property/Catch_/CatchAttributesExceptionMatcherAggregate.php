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
        private readonly iterable $catchPrints,
    ) {
    }

    public function getExceptionMatchers(): Iterator
    {
        foreach ($this->catchPrints as $catchPrint) {
            yield $catchPrint->bind($this->property);
        }
    }
}
