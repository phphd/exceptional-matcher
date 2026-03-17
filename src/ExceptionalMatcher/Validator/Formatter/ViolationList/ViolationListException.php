<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Validator\Formatter\ViolationList;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

/** @api */
interface ViolationListException extends Throwable
{
    public function getViolationList(): ConstraintViolationListInterface;
}
