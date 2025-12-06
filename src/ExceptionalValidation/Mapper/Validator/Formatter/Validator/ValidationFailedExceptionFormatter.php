<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Validator;

use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\ExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\ViolationList\ViolationListException;
use PhPhD\ExceptionalValidation\Rule\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\MatchedException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * @api
 *
 * @implements ExceptionViolationFormatter<ValidationFailedException>
 */
final readonly class ValidationFailedExceptionFormatter implements ExceptionViolationFormatter
{
    public function __construct(
        /** @var ExceptionViolationFormatter<ViolationListException> */
        private MatchedExceptionFormatter $violationListExceptionFormatter,
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
