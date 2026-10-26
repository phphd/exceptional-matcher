<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified\Symfony\Validator\Embedded;

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
