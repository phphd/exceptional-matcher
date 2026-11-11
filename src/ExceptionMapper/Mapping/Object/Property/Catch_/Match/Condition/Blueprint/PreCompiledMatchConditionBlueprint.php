<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Blueprint;

use PhPhD\ExceptionMapper\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\MatchCondition;
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
    public function bind(?ExceptionMappingNode $node = null): MatchCondition
    {
        return $this->condition;
    }
}
