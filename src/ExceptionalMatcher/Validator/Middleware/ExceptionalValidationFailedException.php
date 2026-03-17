<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Validator\Middleware;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

/** @api */
interface ExceptionalValidationFailedException extends Throwable
{
    public function getViolatingMessage(): object;

    public function getViolationList(): ConstraintViolationListInterface;
}
