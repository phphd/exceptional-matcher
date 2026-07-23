<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler;

use PhPhD\ExceptionalMatcher\Rule\MappingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchCondition;
use Throwable;

/**
 * @api
 *
 * @template T of Throwable
 */
interface MatchConditionBlueprint
{
    /** @return MatchCondition<T> */
    public function bind(MappingRule $rule): MatchCondition;
}
