<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Origin;

use PhPhD\ExceptionalValidation\Catch_;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchConditionFactory;
use Throwable;

/**
 * @internal
 *
 * @implements MatchConditionFactory<Throwable>
 */
final class ExceptionOriginMatchConditionFactory implements MatchConditionFactory
{
    public function getCondition(Catch_ $catch, CaptureRule $parent): ?MatchCondition
    {
        $origin = $catch->getFrom();

        if (null === $origin) {
            return null;
        }

        return new ExceptionOriginMatchCondition(...$origin);
    }
}
