<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Validator\Formatter\ViolationList;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Throwable;

/**
 * @api
 *
 * @see ValidationFailedException from Symfony
 */
interface ViolationListException extends Throwable
{
    public function getViolations(): ConstraintViolationListInterface;
}
