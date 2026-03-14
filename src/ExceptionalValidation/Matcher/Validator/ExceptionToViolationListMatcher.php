<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Matcher\Validator;

use PhPhD\ExceptionalValidation\Matcher\ExceptionMatcher;
use PhPhD\ExceptionalValidation\Rule\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\MatchedExceptionList;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

/**
 * @internal
 *
 * @implements ExceptionMatcher<ConstraintViolationListInterface>
 */
final class ExceptionToViolationListMatcher implements ExceptionMatcher
{
    /** @api */
    public function __construct(
        /** @var ExceptionMatcher<MatchedExceptionList> */
        private readonly ExceptionMatcher $matcher,
        /** @var MatchedExceptionFormatter<Throwable,ConstraintViolationInterface> */
        private readonly MatchedExceptionFormatter $formatter,
    ) {
    }

    public function map(object $message, Throwable $exception): ?ConstraintViolationListInterface
    {
        $matchedExceptionList = $this->matcher->map($message, $exception);

        if (null === $matchedExceptionList) {
            return null;
        }

        $violations = $matchedExceptionList->format($this->formatter);

        return new ConstraintViolationList($violations);
    }
}
