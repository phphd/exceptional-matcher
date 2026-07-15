<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Validator\Middleware;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

/** @api */
interface ExceptionalValidationFailedException extends Throwable
{
    public function getViolatingMessage(): object;

    public function getViolations(): ConstraintViolationListInterface;
}
