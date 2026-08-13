<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Matcher;

use Iterator;

/** @internal */
interface ExceptionMatcherAggregate
{
    /** @return Iterator<ExceptionMatcher> */
    public function getExceptionMatchers(): Iterator;
}
