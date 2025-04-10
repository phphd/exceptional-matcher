<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator;

use PhPhD\ExceptionalValidation\Mapper\ExceptionMapper;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\List\ExceptionListViolationFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\CapturedException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

/**
 * @internal
 *
 * @implements ExceptionMapper<ConstraintViolationListInterface>
 */
final class ExceptionViolationListMapper implements ExceptionMapper
{
    public function __construct(
        /** @var ExceptionMapper<non-empty-list<CapturedException<Throwable>>> */
        private readonly ExceptionMapper $mapper,
        private readonly ExceptionListViolationFormatter $violationListFormatter,
    ) {
    }

    public function map(object $message, Throwable $exception): ?ConstraintViolationListInterface
    {
        $capturedExceptionList = $this->mapper->map($message, $exception);

        if (null === $capturedExceptionList) {
            return null;
        }

        return $this->violationListFormatter->format($capturedExceptionList);
    }
}
