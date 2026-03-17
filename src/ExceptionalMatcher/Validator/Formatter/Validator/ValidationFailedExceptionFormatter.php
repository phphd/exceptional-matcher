<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Validator\Formatter\Validator;

use PhPhD\ExceptionalMatcher\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Exception\MatchedException;
use PhPhD\ExceptionalMatcher\Validator\Formatter\ExceptionViolationFormatter;
use PhPhD\ExceptionalMatcher\Validator\Formatter\ViolationList\ViolationListException;
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
