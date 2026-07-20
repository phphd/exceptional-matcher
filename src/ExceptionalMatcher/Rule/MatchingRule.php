<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule;

use PhPhD\ExceptionalMatcher\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Rule\Matcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Path\PropertyPath;

/** @api */
interface MatchingRule extends ExceptionMatcher
{
    /** Returns TRUE if all exceptions were matched; FALSE otherwise */
    public function process(ExceptionReciprocal $reciprocal): bool;

    /** @deprecated - is it needed */
    public function getOwner(): ?self;

    public function getPropertyPath(): PropertyPath;

    public function getEnclosingObject(): object;

    public function getRootObject(): object;

    public function getValue(): mixed;
}
