<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Formatter\Item\Validator;

use PhPhD\ExceptionalValidation\Formatter\Item\ExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Formatter\Item\ViolationList\ViolationListException;
use PhPhD\ExceptionalValidation\Rule\Exception\CapturedException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * @api
 *
 * @implements ExceptionViolationFormatter<ValidationFailedException>
 */
final class ValidationFailedExceptionFormatter implements ExceptionViolationFormatter
{
    public function __construct(
        /** @var ExceptionViolationFormatter<ViolationListException> */
        private readonly ExceptionViolationFormatter $violationListExceptionFormatter,
    ) {
    }

    /**
     * @param CapturedException<ValidationFailedException> $capturedException
     *
     * @return non-empty-list<ConstraintViolation>
     */
    public function format(CapturedException $capturedException): array
    {
        $exception = $capturedException->getException();

        $targetException = new CapturedException(
            new ValidationFailedExceptionAdapter($exception),
            $capturedException->getMatchedRule(),
        );

        return $this->violationListExceptionFormatter->format($targetException);
    }
}
