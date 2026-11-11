<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Matcher;

use Iterator;

/** @internal */
interface ExceptionMatcherAggregate
{
    /** @return Iterator<ExceptionMatcher> */
    public function getExceptionMatchers(): Iterator;
}
