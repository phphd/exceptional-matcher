<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Origin;

use PhPhD\ExceptionalValidation\Capture;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchCondition;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchConditionFactory;

/** @internal */
final class ExceptionOriginMatchConditionFactory implements MatchConditionFactory
{
    public function getCondition(Capture $capture, CaptureRule $parent): ?MatchCondition
    {
        $origin = $capture->getFrom();

        if (null === $origin) {
            return null;
        }

        return new ExceptionOriginMatchCondition(...$origin);
    }
}
