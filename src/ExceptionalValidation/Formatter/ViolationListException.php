<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Formatter;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

/** @api */
interface ViolationListException extends Throwable
{
    public function getViolationList(): ConstraintViolationListInterface;
}
