<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule;

use PhPhD\ExceptionalMatcher\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalMatcher\Rule\Matcher\ExceptionMatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Path\PropertyPath;

/** @api */
interface MappingRule extends ExceptionMatchingRule
{
    public function getOwner(): ?self;

    public function getPropertyPath(): PropertyPath;

    public function getEnclosingObject(): object;

    public function getRootObject(): object;

    public function getValue(): mixed;
}
