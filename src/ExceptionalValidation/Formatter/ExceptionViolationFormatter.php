<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Formatter;

use PhPhD\ExceptionalValidation\Model\Exception\CapturedException;
use Symfony\Component\Validator\ConstraintViolation;

/** @api */
interface ExceptionViolationFormatter
{
    /** @return non-empty-list<ConstraintViolation> */
    public function format(CapturedException $capturedException): array;
}
