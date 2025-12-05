<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Validator;

use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\ExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\ViolationList\ViolationListException;
use PhPhD\ExceptionalValidation\Rule\Exception\Formatter\PropriatedExceptionFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\PropriatedException;
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
        private PropriatedExceptionFormatter $violationListExceptionFormatter,
    ) {
    }

    /** @param PropriatedException<ValidationFailedException> $propriatedException */
    public function format(PropriatedException $propriatedException): array
    {
        $exception = $propriatedException->getException();

        $targetException = new PropriatedException(
            new ValidationFailedExceptionAdapter($exception),
            $propriatedException->getMatchedRule(),
        );

        return $this->violationListExceptionFormatter->format($targetException);
    }
}
