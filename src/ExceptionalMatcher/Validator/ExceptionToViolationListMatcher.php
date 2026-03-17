<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Validator;

use PhPhD\ExceptionalMatcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Rule\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Rule\Exception\MatchedExceptionList;
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

    public function match(Throwable $exception, object $message): ?ConstraintViolationListInterface
    {
        $matchedExceptionList = $this->matcher->match($exception, $message);

        if (null === $matchedExceptionList) {
            return null;
        }

        $violations = $matchedExceptionList->format($this->formatter);

        return new ConstraintViolationList($violations);
    }
}
