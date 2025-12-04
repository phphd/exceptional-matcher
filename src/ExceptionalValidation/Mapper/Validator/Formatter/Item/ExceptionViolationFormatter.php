<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item;

use PhPhD\ExceptionalValidation\Rule\Exception\CapturedException;
use Symfony\Component\Validator\ConstraintViolation;
use Throwable;

/**
 * @api
 *
 * @phpstan-template-contravariant T of Throwable
 *
 * @psalm-template-covariant T of Throwable (psalm doesn't support contravariant templates)
 *
 * @psalm-immutable
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
