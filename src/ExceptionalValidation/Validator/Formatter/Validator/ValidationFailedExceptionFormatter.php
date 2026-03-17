<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Validator\Formatter\Validator;

use PhPhD\ExceptionalValidation\Rule\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\MatchedException;
use PhPhD\ExceptionalValidation\Validator\Formatter\ExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Validator\Formatter\ViolationList\ViolationListException;
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
        private readonly MatchedExceptionFormatter $violationListExceptionFormatter,
    ) {
    }

    /** @param MatchedException<ValidationFailedException> $matchedException */
    public function format(MatchedException $matchedException): array
    {
        $exception = $matchedException->getException();

        $targetException = new MatchedException(
            new ValidationFailedExceptionAdapter($exception),
            $matchedException->getRule(),
        );

        return $this->violationListExceptionFormatter->format($targetException);
    }
}
