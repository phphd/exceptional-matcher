<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Validator\Formatter\ViolationList;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Throwable;

/**
 * @api
 *
 * Also, {@see ValidationFailedException} of Symfony
 */
interface ViolationsEmbeddedException extends Throwable
{
    public function getViolations(): ConstraintViolationListInterface;
}
