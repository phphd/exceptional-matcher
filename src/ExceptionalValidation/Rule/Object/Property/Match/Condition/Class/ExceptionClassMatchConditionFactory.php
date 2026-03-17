<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Condition\Class;

use PhPhD\ExceptionalValidation\Rule\MatchingRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Condition\MatchConditionFactory;
use Throwable;

/**
 * @internal
 *
 * @implements MatchConditionFactory<Throwable>
 */
final class ExceptionClassMatchConditionFactory implements MatchConditionFactory
{
    public function getCondition(Catch_ $catch, MatchingRule $parent): ExceptionClassMatchCondition
    {
        return new ExceptionClassMatchCondition($catch->getExceptionClass());
    }
}
