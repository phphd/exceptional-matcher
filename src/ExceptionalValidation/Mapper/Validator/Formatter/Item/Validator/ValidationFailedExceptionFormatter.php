<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Validator;

use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\PropriatedExceptionFormatter;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\ViolationList\ViolationListException;
use PhPhD\ExceptionalValidation\Rule\Exception\PropriatedException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * @api
 *
 * @implements PropriatedExceptionFormatter<ValidationFailedException>
 */
final readonly class ValidationFailedExceptionFormatter implements PropriatedExceptionFormatter
{
    public function __construct(
        /** @var PropriatedExceptionFormatter<ViolationListException> */
        private PropriatedExceptionFormatter $violationListExceptionFormatter,
    ) {
    }

    /**
     * @param PropriatedException<ValidationFailedException> $propriatedException
     *
     * @return non-empty-list<ConstraintViolation>
     */
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
