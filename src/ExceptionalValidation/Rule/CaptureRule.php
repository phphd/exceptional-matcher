<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule;

use PhPhD\ExceptionalValidation\Rule\Exception\ExceptionPackage;
use PhPhD\ExceptionalValidation\Rule\Path\PropertyPath;

/** @internal */
interface CaptureRule
{
    /** Returns TRUE if all given exceptions were captured, or FALSE if not */
    public function process(ExceptionPackage $package): bool;

    public function getPropertyPath(): PropertyPath;

    public function getEnclosingObject(): object;

    public function getRoot(): object;

    public function getValue(): mixed;
}
