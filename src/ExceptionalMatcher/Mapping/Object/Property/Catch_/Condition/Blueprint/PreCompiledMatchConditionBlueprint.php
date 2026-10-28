<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Blueprint;

use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\MatchCondition;
use Throwable;

/**
 * @api
 *
 * @template T of Throwable
 *
 * @implements MatchConditionBlueprint<T>
 */
final class PreCompiledMatchConditionBlueprint implements MatchConditionBlueprint
{
    public function __construct(
        /** @var MatchCondition<T> */
        private readonly MatchCondition $condition,
    ) {
    }

    /** @return MatchCondition<T> */
    public function bind(ExceptionMappingNode $node): MatchCondition
    {
        return $this->condition;
    }
}
