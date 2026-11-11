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
 */
interface MatchConditionBlueprint
{
    /** @return MatchCondition<T> */
    public function bind(ExceptionMappingNode $node): MatchCondition;
}
