<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Port\Symfony\Validator\Middleware\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

/** @api */
interface ExceptionalValidationFailedException extends Throwable
{
    public function getViolatingMessage(): object;

    public function getViolations(): ConstraintViolationListInterface;
}
