<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Middleware;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

/** @api */
interface ExceptionalValidationFailedException extends Throwable
{
    public function getViolatingMessage(): object;

    public function getViolationList(): ConstraintViolationListInterface;
}
