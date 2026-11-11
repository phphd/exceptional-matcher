<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Matcher;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\ExceptionReciprocal;

/** @internal */
interface ExceptionMatcher
{
    /** Returns TRUE if all exceptions were matched; FALSE otherwise */
    public function match(ExceptionReciprocal $reciprocal): bool;
}
