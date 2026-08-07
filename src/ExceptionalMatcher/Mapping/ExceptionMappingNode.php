<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping;

use PhPhD\ExceptionalMatcher\Mapping\Object\_Property\Path\PropertyPath;
use PhPhD\ExceptionalMatcher\Rule\Matcher\ExceptionMatchingRule;

/** @api */
interface ExceptionMappingNode extends ExceptionMatchingRule
{
    public function getOwner(): ?self;

    public function getPropertyPath(): PropertyPath;

    public function getEnclosingObject(): object;

    public function getRootObject(): object;

    public function getValue(): mixed;
}
