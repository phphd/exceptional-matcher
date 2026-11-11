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
 */
interface MatchConditionBlueprint
{
    /** @return MatchCondition<T> */
    public function bind(ExceptionMappingNode $node): MatchCondition;
}
