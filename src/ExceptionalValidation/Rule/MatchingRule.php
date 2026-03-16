<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule;

use PhPhD\ExceptionalValidation\Rule\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Path\PropertyPath;

/** @internal */
interface MatchingRule
{
    /** Returns TRUE if all exceptions were matched; FALSE otherwise */
    public function process(ExceptionReciprocal $reciprocal): bool;

    public function getParent(): ?self;

    public function getPropertyPath(): PropertyPath;

    public function getEnclosingObject(): object;

    public function getRootObject(): object;

    public function getValue(): mixed;
}
