<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item;

use PhPhD\ExceptionalValidation\Rule\Exception\CapturedException;
use Symfony\Component\Validator\ConstraintViolation;
use Throwable;

/**
 * @api
 *
 * @template T of Throwable
 */
interface ExceptionViolationFormatter
{
    /**
     * @param CapturedException<T> $capturedException
     *
     * @return non-empty-list<ConstraintViolation>
     */
    public function format(CapturedException $capturedException): array;
}
