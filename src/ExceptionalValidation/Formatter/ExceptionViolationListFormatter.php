<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Formatter;

use PhPhD\ExceptionalValidation\Model\Exception\CapturedException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/** @api */
interface ExceptionViolationListFormatter
{
    /** @param non-empty-list<CapturedException> $capturedExceptions */
    public function formatViolations(array $capturedExceptions): ConstraintViolationListInterface;
}
