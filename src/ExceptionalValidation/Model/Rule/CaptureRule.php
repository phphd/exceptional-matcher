<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Rule;

use PhPhD\ExceptionalValidation\Model\Exception\ExceptionPackage;
use PhPhD\ExceptionalValidation\Model\ValueObject\PropertyPath;

/** @internal */
interface CaptureRule
{
    /** Returns TRUE if all exceptions were captured and FALSE otherwise */
    public function process(ExceptionPackage $package): bool;

    public function getPropertyPath(): PropertyPath;

    public function getEnclosingObject(): object;

    public function getRoot(): object;

    public function getValue(): mixed;
}
