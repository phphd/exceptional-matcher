<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Formatter\List;

use PhPhD\ExceptionalValidation\Rule\Exception\CapturedException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

/** @api */
interface ExceptionListViolationFormatter
{
    /** @param non-empty-list<CapturedException<Throwable>> $capturedExceptionList */
    public function format(array $capturedExceptionList): ConstraintViolationListInterface;
}
