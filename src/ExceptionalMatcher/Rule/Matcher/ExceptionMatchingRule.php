<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Matcher;

use PhPhD\ExceptionalMatcher\Exception\ExceptionReciprocal;

/** @internal */
interface ExceptionMatchingRule
{
    /** Returns TRUE if all exceptions were matched; FALSE otherwise */
    public function process(ExceptionReciprocal $reciprocal): bool;
}
