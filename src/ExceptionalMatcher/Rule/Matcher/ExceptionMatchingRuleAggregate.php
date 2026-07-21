<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Matcher;

use Iterator;

/** @internal */
interface ExceptionMatchingRuleAggregate
{
    /** @return Iterator<ExceptionMatchingRule> */
    public function getExceptionMatchingRules(): Iterator;
}
