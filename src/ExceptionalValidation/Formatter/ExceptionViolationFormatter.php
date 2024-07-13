<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Formatter;

use PhPhD\ExceptionalValidation\Model\Exception\CapturedException;
use Symfony\Component\Validator\ConstraintViolationInterface;

/** @api */
interface ExceptionViolationFormatter
{
    public function formatViolation(CapturedException $capturedException): ConstraintViolationInterface;
}
