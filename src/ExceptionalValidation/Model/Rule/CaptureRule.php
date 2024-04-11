<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Rule;

use PhPhD\ExceptionalValidation\Model\ValueObject\CaughtException;
use PhPhD\ExceptionalValidation\Model\ValueObject\PropertyPath;
use PhPhD\ExceptionalValidation\Model\ValueObject\ThrownExceptions;

interface CaptureRule
{
    /** @return list<CaughtException> */
    public function capture(ThrownExceptions $thrownExceptions): array;

    public function getPropertyPath(): PropertyPath;

    public function getEnclosingObject(): object;

    public function getRoot(): object;

    public function getValue(): mixed;
}
