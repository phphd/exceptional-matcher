<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping;

use PhPhD\ExceptionMapper\Mapping\Matcher\ExceptionMatcher;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Path\PropertyPath;

/** @api */
interface ExceptionMappingNode extends ExceptionMatcher
{
    public function getOwner(): ?self;

    public function getRootObject(): object;

    public function getLeafObject(): object;

    public function getPropertyPath(): PropertyPath;

    public function getValue(): mixed;
}
